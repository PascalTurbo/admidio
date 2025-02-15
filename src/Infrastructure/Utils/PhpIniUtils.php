<?php
namespace Admidio\Infrastructure\Utils;

/**
 * @brief Class manages PHP-Ini stuff
 *
 * @copyright The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 */
final class PhpIniUtils
{
    public const BYTES_UNIT_FACTOR_1024 = 1024;
    public const BYTES_UNIT_FACTOR_1000 = 1000;

    /**
     * @var array<int,string> The disabled function names
     */
    private static array $disabledFunctions;

    /**
     * Returns the disabled function names
     * @return array<int,string> Returns the disabled function names
     * @see https://www.php.net/manual/en/ini.core.php#ini.disable-functions
     */
    public static function getDisabledFunctions(): array
    {
        if (!isset(self::$disabledFunctions)) {
            self::$disabledFunctions = explode(',', ini_get('disable_functions'));
        }

        return self::$disabledFunctions;
    }

    /**
     * Checks if the size limits have valid values because they depend on each other
     * @return bool
     */
    public static function checkSizeLimits(): bool
    {
        return (is_infinite(self::getMemoryLimit()) || self::getMemoryLimit() >= self::getPostMaxSize())
            && (is_infinite(self::getPostMaxSize()) || self::getPostMaxSize() >= self::getFileUploadMaxFileSize());
    }

    /**
     * Returns the calculated bytes of a string or INF if unlimited.
     * @param string $data  Could be empty string (not set), "-1" (no limit) or a float with a unit.
     *                      Units could be K for Kilobyte, M for Megabyte, G for Gigabyte or T for Terabyte.
     * @param int $multi Factor to multiply. Default: 1024
     * @return float Returns the bytes of the data string.
     */
    private static function getBytesFromSize(string $data, int $multi = self::BYTES_UNIT_FACTOR_1024): float
    {
        if ($data === '' || $data === '-1') {
            return INF;
        }

        $value = (float) substr($data, 0, -1);
        $unit  = strtoupper(substr($data, -1));

        switch ($unit) {
            case 'T': // fallthrough
                $value *= $multi;
                // no break
            case 'G': // fallthrough
                $value *= $multi;
                // no break
            case 'M': // fallthrough
                $value *= $multi;
                // no break
            case 'K': // fallthrough
                $value *= $multi;
        }

        return $value;
    }

    /**
     * Returns the allowed base-dirs
     * @return array<string,string>
     * @see https://www.php.net/manual/en/ini.core.php#ini.open-basedir
     */
    public static function getBaseDirs(): array
    {
        return explode(PATH_SEPARATOR, ini_get('open_basedir'));
    }

    /**
     * Returns the memory limit
     * @return float
     * @see https://www.php.net/manual/en/ini.core.php#ini.memory-limit
     */
    public static function getMemoryLimit(): float
    {
        return self::getBytesFromSize(ini_get('memory_limit'));
    }

    /**
     * Returns the maximum post size
     * @return int
     * @see https://www.php.net/manual/en/ini.core.php#ini.post-max-size
     */
    public static function getPostMaxSize(): int
    {
        return self::getBytesFromSize(ini_get('post_max_size'));
    }

    /**
     * Returns the file upload temporary directory
     * @return string
     * @see https://www.php.net/manual/en/ini.core.php#ini.upload-tmp-dir
     */
    public static function getFileUploadTmpDir(): string
    {
        return ini_get('upload_tmp_dir');
    }

    /**
     * Returns the maximum upload filesize
     * @return int
     * @see https://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize
     */
    public static function getFileUploadMaxFileSize(): int
    {
        return self::getBytesFromSize(ini_get('upload_max_filesize'));
    }

    /**
     * Returns the maximum file upload count
     * @return int
     * @see https://www.php.net/manual/en/ini.core.php#ini.max-file-uploads
     */
    public static function getFileUploadMaxFileCount(): int
    {
        return (int) ini_get('max_file_uploads');
    }

    /**
     * Returns the maximum upload size out of memory-limit, max-post-size and max-file-size
     * @return int
     */
    public static function getUploadMaxSize(): int
    {
        return (int) min(self::getMemoryLimit(), self::getPostMaxSize(), self::getFileUploadMaxFileSize());
    }

    /**
     * Returns if file-upload is enabled
     * @return bool
     * @see https://www.php.net/manual/en/ini.core.php#ini.file-uploads
     */
    public static function isFileUploadEnabled(): bool
    {
        return (bool) ini_get('file_uploads');
    }

    /**
     * Checks if a given directory path is in the allowed base-directories
     * @param string $directoryPath The directory path to check
     * @return bool
     */
    private static function isInBaseDirs(string $directoryPath): bool
    {
        $baseDirs = self::getBaseDirs();

        if ($baseDirs[0] === '') {
            return true;
        }

        foreach ($baseDirs as $baseDir) {
            if (strpos($directoryPath, $baseDir) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a given directory path exists and is in the base-directories
     * @param string $directoryPath The directory path to check
     * @throws \UnexpectedValueException Throws if a given directory does not exist
     * @throws \RuntimeException         Throws if a given directory is not in the base-directories
     */
    private static function checkIsValidDir(string &$directoryPath)
    {
        $directoryPath = FileSystemUtils::getNormalizedPath($directoryPath);

        if (!is_dir($directoryPath)) {
            throw new \UnexpectedValueException('Directory "' . $directoryPath . '" does not exist!');
        }
        if (!self::isInBaseDirs($directoryPath)) {
            throw new \RuntimeException('Directory "' . $directoryPath . '" is not in base-directories!');
        }
    }

    /**
     * Sets the allowed base-directories
     * @param array<int,string> $directoryPaths The directory paths to set as allowed base-dirs
     * @throws \UnexpectedValueException Throws if a given directory does not exist
     * @throws \RuntimeException         Throws if a given directory is not in the base-directories
     * @return bool|string
     * @see https://www.php.net/manual/en/ini.core.php#ini.open-basedir
     */
    public static function setBaseDirs(array $directoryPaths = array())
    {
        foreach ($directoryPaths as &$directoryPath) {
            self::checkIsValidDir($directoryPath);
        }
        unset($directoryPath);

        return ini_set('open_basedir', implode(PATH_SEPARATOR, $directoryPaths));
    }

    /**
     * Sets the file upload temporary directory
     * @param string $directoryPath The directory path to set the file upload temporary directory
     * @return bool|string
     * @throws \RuntimeException         Throws if a given directory is not in the base-directories
     * @throws \UnexpectedValueException Throws if a given directory does not exist
     * @see https://www.php.net/manual/en/ini.core.php#ini.upload-tmp-dir
     */
    public static function setUploadTmpDir(string $directoryPath)
    {
        self::checkIsValidDir($directoryPath);

        return ini_set('upload_tmp_dir', $directoryPath);
    }

    /**
     * Starts a new execution time limit
     * @param int $seconds Execution time limit in seconds
     * @throws \RuntimeException Throws if starting a new execution time limit failed
     * @see https://www.php.net/manual/en/function.set-time-limit.php
     * @see https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time
     */
    public static function startNewExecutionTimeLimit(int $seconds)
    {
        global $gDebug, $gLogger;

        if (in_array('set_time_limit', self::getDisabledFunctions(), true)) {
            return;
        }

        // @ prevents error output in safe-mode
        $result = @set_time_limit($seconds);
        if (!$result && $gDebug) {
            $gLogger->warning('Function set_time_limit failed');
        }
    }
}
