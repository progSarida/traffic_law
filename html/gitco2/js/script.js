$('#CityId option').on('click', function(){
      var CityId = $(this).val();
      $.ajax('req_new.php?cityId'+CityId,function(){
      	//code here
      });
});