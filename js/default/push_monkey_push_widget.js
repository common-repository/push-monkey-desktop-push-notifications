jQuery(document).ready(function($) {

		$('#push-monkey-push-dashboard-widget #pm_title, #push-monkey-push-dashboard-widget #pm_message')
		.unbind('keyup change input paste').bind('keyup change input paste',function(e){

			var $this = $(this);
			var val = $this.val();
			var valLength = val.length;
			var maxCount = $this.attr('maxlength');
			if(valLength > maxCount){

				$this.val($this.val().substring(0, maxCount));
			}
		}); 
		$('.push_monkey_submit').click(function(){

			$('#push-monkey-push-dashboard-widget form').submit();
		});

		$('.push-monkey-push-widget-send').click(function(ev){

			ev.preventDefault();
			$('#push_monkey_confirmation_modal').modal();
		});
});
