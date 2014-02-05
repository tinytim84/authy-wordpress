jQuery(document).ready(function($){
	//Create variables
	var $authy_stats = $("#authy_enable_dashboard_stats");
	var $authy_months = $("#authy_number_of_months_to_show");

	//Set the initial state of the "Number of Months" input
	if ($authy_stats.prop("checked") === true) enableMonths();
	else disableMonths();

	//Add an event listener for the change of "Enable Dashboard Stats" checkbox
	$authy_stats.change(function(){
		toggleMonths();
	});

	/**
	* Checks if "Number of Months" input is diabled
	* @return bool
	*/
	function monthsDisabled() {
		return $authy_months.attr('disabled');
	}

	//Toggle state of "Number of Months" input
	function toggleMonths() {
		if (monthsDisabled()) enableMonths();
		else disableMonths();
	}

	//Enable "Number of Months" input
	function enableMonths() {
		if (monthsDisabled()) $authy_months.removeAttr('disabled');
	}

	//Disable "Number of Months" input
	function disableMonths() {
		$authy_months.attr('disabled', 'disabled');
	}
});