<?php
class phive_booking_cache_manager
{
	public function __construct() 
	{
		
	}

	public function ph_is_cache_set($id=null)
	{
		if ($id != null && $id != '') 
		{
			$transient_set_key = "booked_dates_transient_set_".$id;
			$value = get_transient($transient_set_key);
			if (!empty($value)) 
			{
				return $value;
			}
			else
			{
				return 'No';
			}
		}
		else
		{
			return 'No';
		}
	}

	public function ph_set_cache($id=null, $values='')
	{
		if ($id != null && $id != '') 
		{
			$key = "booked_dates_".$id;
			set_transient($key, $values, 0);
			$transient_set_key = "booked_dates_transient_set_".$id;
			set_transient($transient_set_key, 'Yes', 0);
		}
	}

	public function ph_get_cache($id=null)
	{
		if ($id != null && $id != '') 
		{
			$key = "booked_dates_".$id;
			return get_transient($key);
		}
		else
		{
			return false;
		}
	}

	public function ph_unset_cache($id=null)
	{
		if ($id != null && $id != '') 
		{
			$transient_set_key = "booked_dates_transient_set_".$id;
			set_transient($transient_set_key, 'No', 0);
		}
	}
};
