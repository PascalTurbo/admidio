<?php
/*****************************************************************************/
/** @class ProfileFields
 *  @brief Reads the user fields structure out of database and give access to it
 *
 *  When an object is created than the actual profile fields structure will
 *  be read. In addition to this structure you can read the user values for
 *  all fields if you call @c readUserData . If you read field values than
 *  you will get the formated output. It's also possible to set user data and
 *  save this data to the database
 */
/*****************************************************************************
 *
 *  Copyright    : (c) 2004 - 2012 The Admidio Team
 *  Homepage     : http://www.admidio.org
 *  License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

require_once(SERVER_PATH. '/adm_program/system/classes/table_user_field.php');

class ProfileFields
{
    public    $mProfileFields = array();///< Array with all user fields objects
    public    $mUserData = array();		///< Array with all user data objects

	protected $mOrganization;			///< organization object
	protected $mUserId;					///< UserId of the current user of this object
	public 	  $mDb;						///< db object must public because of session handling
    protected $noValueCheck;    		///< if true, than no value will be checked if method setValue is called
    public    $columnsValueChanged;     ///< flag if a value of one field had changed

	/** constructor that will initialize variables and read the profile field structure
	 *  @param $db Database object (should be @b $gDb)
	 *  @param $organization Organization object (should be @b $gOrganization)
	 */
    public function __construct(&$db, &$organization)
    {
		$this->mDb =& $db;
		$this->mOrganization = $organization;
		$this->readProfileFields();
		$this->mUserId = 0;
		$this->noValueCheck = false;
		$this->columnsValueChanged = false;
    }
	
	/** user data of all profile fields will be initialized
	 *  the fields array will not be renewed
	 */
	public function clearUserData()
	{
		$this->mUserData = array();
		$this->mUserId = 0;
		$this->columnsValueChanged = false;
	}
	
	/** returns for a fieldname intern (usf_name_intern) the value of the column from table adm_user_fields
	 *  @param $fieldNameIntern Expects the @b usf_name_intern of table @b adm_user_fields
	 *  @param $column The column name of @b adm_user_field for which you want the value
	 *  @param $format Optional the format (is neccessary for timestamps)
	 */
	public function getProperty($fieldNameIntern, $column, $format = '')
	{
		if(array_key_exists($fieldNameIntern, $this->mProfileFields))
		{
			return $this->mProfileFields[$fieldNameIntern]->getValue($column, $format);
		}

		// if id-field not exists then return zero
		if(strpos($column, '_id') > 0)
		{
			return 0;
		}
        return null;
	}
	
	/** returns for field id (usf_id) the value of the column from table adm_user_fields
	 *  @param $fieldId Expects the @b usf_id of table @b adm_user_fields
	 *  @param $column The column name of @b adm_user_field for which you want the value
	 *  @param $format Optional the format (is neccessary for timestamps)
	 */
    public function getPropertyById($fieldId, $column, $format = '')
    {
        foreach($this->mProfileFields as $field)
        {
            if($field->getValue('usf_id') == $fieldId)
            {
                return $field->getValue($column, $format);
            }
        }
        return null;
    }

	/** Returns the value of the field in html format with consideration of all layout parameters
	 *  @param $fieldNameIntern Internal profile field name of the field that should be html formated
	 *  @param $value The value that should be formated must be commited so that layout is also possible for values that aren't stored in database
	 *  @param $value2 An optional parameter that is necessary for some special fields like email to commit the user id
	 *  @return Returns an html formated string that considered the profile field settings
	 */
	public function getHtmlValue($fieldNameIntern, $value, $value2 = '')
	{
		global $gPreferences, $g_root_path;

		if(strlen($value) > 0
		&& array_key_exists($fieldNameIntern, $this->mProfileFields) == true)
		{
			// create html for each field type
			$htmlValue = $value;

			if($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'CHECKBOX')
			{
				if($value == 1)
				{
					$htmlValue = '<img src="'.THEME_PATH.'/icons/checkbox_checked.gif" alt="on" />';
				}
				else
				{
					$htmlValue = '<img src="'.THEME_PATH.'/icons/checkbox.gif" alt="off" />';
				}
			}
			elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'EMAIL')
			{
				// the value in db is only the position, now search for the text
				if(strlen($value) > 0)
				{				    
					if($gPreferences['enable_mail_module'] != 1)
					{
						$emailLink = 'mailto:'.$value;
					}
					else
					{
    				    // set value2 to user id because we need a second parameter in the link to mail module
    				    if(strlen($value2) == 0)
    				    {
        				    $value2 = $this->mUserId;
    				    }
    				    
						$emailLink = $g_root_path.'/adm_program/modules/mail/mail.php?usr_id='. $value2;
					}
					if(strlen($value) > 30)
					{
						$htmlValue = '<a href="'.$emailLink.'" title="'.$value.'">'.substr($value, 0, 30).'...</a>';
					}
					else
					{
						$htmlValue = '<a href="'.$emailLink.'" style="overflow: visible; display: inline;" title="'.$value.'">'.$value.'</a>';;
					}
				}
			}
			elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'URL')
			{
				if(strlen($value) > 0)
				{
					if(strlen($value) > 35)
					{
						$htmlValue = '<a href="'. $value.'" target="_blank" title="'. $value.'">'. substr($value, strpos($value, '//') + 2, 35). '...</a>';
					}
					else
					{
						$htmlValue = '<a href="'. $value.'" target="_blank" title="'. $value.'">'. substr($value, strpos($value, '//') + 2). '</a>';
					}
				}
			}
			elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'TEXT_BIG')
			{
				$htmlValue = nl2br($value);
			}
		
			// if field has url then create a link
			if(strlen($this->mProfileFields[$fieldNameIntern]->getValue('usf_url')))
			{
				if($fieldNameIntern == 'FACEBOOK' && is_numeric($value))
				{
					// facebook has two different profile urls (id and facebook name), 
					// we could only store one way in database (facebook name) and the other (id) is defined here :)
					$htmlValue = '<a href="http://www.facebook.com/profile.php?id='.$value.'" target="_blank">'.$htmlValue.'</a>';
				}
				else
				{
					$htmlValue = '<a href="'.$this->mProfileFields[$fieldNameIntern]->getValue('usf_url').'" target="_blank">'.$htmlValue.'</a>';
				}
				
				// replace a variable in url with user value
				if(strpos($this->mProfileFields[$fieldNameIntern]->getValue('usf_url'), '%user_content%') !== false)
				{
					$htmlValue = preg_replace ('/%user_content%/', $value,  $htmlValue);

				}
			}
			$value = $htmlValue;
		}
		else
		{
			// special case for type CHECKBOX and no value is there, then show unchecked checkbox
			if(array_key_exists($fieldNameIntern, $this->mProfileFields) == true
			&& $this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'CHECKBOX')
			{
				$value = '<img src="'.THEME_PATH.'/icons/checkbox.gif" alt="off" />';
			}
		}
		return $value;
	}

	/** Returns the user value for this column @n
	 *  format = 'd.m.Y' : a date or timestamp field accepts the format of the PHP date() function @n
	 *  format = 'html'  : returns the value in html-format if this is necessary for that field type @n
	 *  format = 'intern' : returns the value that is stored in database with no format applied
	 *  @param $fieldNameIntern Expects the @b usf_name_intern of table @b adm_user_fields
	 *  @param $format Returns the field value in a special format @b text, @b html, @b intern or datetime (detailed description in method description)
	 */
	public function getValue($fieldNameIntern, $format = '')
	{
		global $gL10n, $gPreferences;
		$value = '';

		// exists a profile field with that name ?
		// then check if user has a data object for this field and then read value of this object
		if(array_key_exists($fieldNameIntern, $this->mProfileFields)
		&& array_key_exists($this->mProfileFields[$fieldNameIntern]->getValue('usf_id'), $this->mUserData)) 
		{
			$value = $this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->getValue('usd_value', $format);

			if($format != 'intern')
			{
				if($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'DATE' && strlen($value) > 0)
				{
					if($format == 'html')
					{
						$dateFormat = $gPreferences['system_date'];
					}
					else
					{
						$dateFormat = $format;
					}
					
					// if date field then the current date format must be used
					$date = new DateTimeExtended($value, 'Y-m-d', 'date');
					if($date->valid() == false)
					{
						return $value;
					}
					$value = $date->format($dateFormat);
				}
				elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'DROPDOWN'
					|| $this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'RADIO_BUTTON')
				{
					// the value in db is only the position, now search for the text
					if($value > 0)
					{
						$arrListValues = $this->mProfileFields[$fieldNameIntern]->getValue('usf_value_list');
						$value = $arrListValues[$value];
						
					}
				}
				elseif($fieldNameIntern == 'COUNTRY' && strlen($value) > 0)
				{
					// read the language name of the country
					$value = $gL10n->getCountryByCode($value);
				}
			}
		}
		
		// get html output for that field type and value
		if($format == 'html')
		{
			$value = $this->getHtmlValue($fieldNameIntern, $value);
		}

		return $value;
	}

    /** If this method is called than all further calls of method @b setValue will not check the values.
	 *  The values will be stored in database without any inspections !
	 */
    public function noValueCheck()
    {
        $this->noValueCheck = true;
    }

	// build an array with the data of all user fields
	// userId : read data from this user
	public function readUserData($userId)
	{
		if(count($this->mProfileFields) == 0)
		{
			$this->readProfileFields();
		}

		if($userId > 0)
		{
			// remember the user
			$this->mUserId = $userId;
			
			// read all user data of user
			$sql = 'SELECT * FROM '.TBL_USER_DATA.', '. TBL_USER_FIELDS. '
					 WHERE usd_usf_id = usf_id
					   AND usd_usr_id = '.$userId;
			$usdResult = $this->mDb->query($sql);

			while($row = $this->mDb->fetch_array($usdResult))
			{
				if(isset($this->mUserData[$row['usd_usf_id']]) == false)
				{
					$this->mUserData[$row['usd_usf_id']] = new TableAccess($this->mDb, TBL_USER_DATA, 'usd');
				}
				$this->mUserData[$row['usd_usf_id']]->setArray($row);
			}
		}
	}

	// build an array with the structure of all user fields
	public function readProfileFields()
	{
		// first initialize existing data
		$this->mProfileFields = array();
		$this->mUserData   = array();

		// read all user fields and belonging category data of organization
		$sql = 'SELECT * FROM '. TBL_CATEGORIES. ', '. TBL_USER_FIELDS. '
                 WHERE usf_cat_id = cat_id
                   AND (  cat_org_id IS NULL
                       OR cat_org_id  = '.$this->mOrganization->getValue('org_id').' )
                 ORDER BY cat_sequence ASC, usf_sequence ASC ';
        $usfResult = $this->mDb->query($sql);

        while($row = $this->mDb->fetch_array($usfResult))
        {
            if(isset($this->mProfileFields[$row['usf_name_intern']]) == false)
            {
                $this->mProfileFields[$row['usf_name_intern']] = new TableUserField($this->mDb);
            }
            $this->mProfileFields[$row['usf_name_intern']]->setArray($row);
        }
	}

	// save data of every user field
	// userId : id is neccessary if new user, that id was not known before
	public function saveUserData($userId)
	{
		$this->mDb->startTransaction();

		foreach($this->mUserData as $key => $value)
		{
			// if new user than set user id
			if($this->mUserId == 0)
			{
				$this->mUserData[$key]->setValue('usd_usr_id', $userId);
			}

			// if value exists and new value is empty then delete entry
			if($this->mUserData[$key]->getValue('usd_id') > 0
			&& strlen($this->mUserData[$key]->getValue('usd_value')) == 0)
			{
				$this->mUserData[$key]->delete();
			}
			else
			{
				$this->mUserData[$key]->save();
			}
		}

        $this->columnsValueChanged = false;
		$this->mUserId = $userId;
		$this->mDb->endTransaction();
	}
	
	// set value for column usd_value of field
    public function setValue($fieldNameIntern, $fieldValue)
    {
        global $gPreferences;
        $returnCode = false;

        if(strlen($fieldValue) > 0)
        {
			if($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'CHECKBOX')
            {
                // Checkbox darf nur 1 oder 0 haben
                if($fieldValue != 0 && $fieldValue != 1 && $this->noValueCheck != true)
                {
                    return false;
                }
            }
            elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'DATE')
            {
                // Datum muss gueltig sein und formatiert werden
                $date = new DateTimeExtended($fieldValue, $gPreferences['system_date'], 'date');
                if($date->valid() == false)
                {
                    if($this->noValueCheck != true)
                    {                        
                        return false;
                    }
                }
                else
                {
                    $fieldValue = $date->format('Y-m-d');
                }
            }
            elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'EMAIL')
            {
                // Email darf nur gueltige Zeichen enthalten und muss einem festen Schema entsprechen
                $fieldValue = admStrToLower($fieldValue);
                if (!strValidCharacters($fieldValue, 'email') && $this->noValueCheck != true)
                {
                    return false;
                }
            }
            elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'NUMERIC')
            {
                // Zahl muss numerisch sein
                if(is_numeric(strtr($fieldValue, ',.', '00')) == false && $this->noValueCheck != true)
                {
                    return false;
                }
            }
            elseif($this->mProfileFields[$fieldNameIntern]->getValue('usf_type') == 'URL')
            {
                // Homepage darf nur gueltige Zeichen enthalten
                if (!strValidCharacters($fieldValue, 'url') && $this->noValueCheck != true)
                {
                    return false;
                }
                // Homepage noch mit http vorbelegen
                if(strpos(admStrToLower($fieldValue), 'http://')  === false
                && strpos(admStrToLower($fieldValue), 'https://') === false )
                {
                    $fieldValue = 'http://'. $fieldValue;
                }
            }

        }

		// first check if user has a data object for this field and then set value of this user field
		if(array_key_exists($this->mProfileFields[$fieldNameIntern]->getValue('usf_id'), $this->mUserData))
		{
			$returnCode = $this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->setValue('usd_value', $fieldValue);
		}
		elseif(isset($this->mProfileFields[$fieldNameIntern]) == true && strlen($fieldValue) > 0)
		{
			$this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')] = new TableAccess($this->mDb, TBL_USER_DATA, 'usd');
			$this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->setValue('usd_usf_id', $this->mProfileFields[$fieldNameIntern]->getValue('usf_id'));
			$this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->setValue('usd_usr_id', $this->mUserId);
			$returnCode = $this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->setValue('usd_value', $fieldValue);
		}
		
		if($returnCode && $this->mUserData[$this->mProfileFields[$fieldNameIntern]->getValue('usf_id')]->columnsValueChanged)
		{
            $this->columnsValueChanged = true;
		}

		return $returnCode;
    }
}
?>