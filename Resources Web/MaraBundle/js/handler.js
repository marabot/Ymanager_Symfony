function onPlayerReady(event) {
    event.target.playVideo();
}


$('document').ready(function (){

    $('#lastHarvestDatePicker .input-group.date').datepicker({
    });

    $('#dateAfter .input-group.date').datepicker({
    });

    $('#dateBefore .input-group.date').datepicker({
    });

    $('#datePicker')
        .datepicker({
            format: 'mm/dd/yyyy'
        })
        .on('changeDate', function(e) {
            // Revalidate the date field
            $('#eventForm').formValidation('revalidateField', 'date');
        });

    $('#datePicker2')
        .datepicker({
            format: 'mm/dd/yyyy'
        })
        .on('changeDate', function(e) {
            // Revalidate the date field
            $('#eventForm').formValidation('revalidateField', 'date');
        });

    $('#eventForm').formValidation({
        framework: 'bootstrap',
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            name: {
                validators: {
                    notEmpty: {
                        message: 'The name is required'
                    }
                }
            },
            date: {
                validators: {
                    notEmpty: {
                        message: 'The date is required'
                    },
                    date: {
                        format: 'MM/DD/YYYY',
                        message: 'The date is not a valid'
                    }
                }
            }
        }
    });
});

// handler delete ChannelsButton
$('#lienDrop').click(function()
{
    $text=$(this).find('a').text();

    $('#dropResult').text($text);
}) ;




// handler changeLastharvest Button
  $('#changeLastHarvest').click(function(){
										
									$botToChange=$('#botToChange').val();
									$newDate=$('#newlastharvest').val();

                              $.ajax(
                                  {
                                      type: "POST",
                                      url: harvest,
                                      data: { botId : $botId , dateAfter : $dateAfter },
                                      cache: false,
                                      success: function ($playListID)
                                      {
                                          if ($playListID)
                                          {
                                              $newVidsContainer='#'+"botNewVids" + $botId;

                                              $($botVidContainer).html('<iframe id="ytplayer" type="text/html" width="640" height="390" src="http://www.youtube.com/embed?listType=playlist&list='+ $.trim($playListID)+'" frameborder="0"/>');

                                          }
                                      }
                                  });

                });




// handler clear all playlist button

$('#clearplaylists').click(function (){

										$.get(
											'servicesPHP_SQL/BDDdeletePlaylists.php',
												function($resp){
														$('#clearplaylistsResp').html($resp);
												}									
											)	
									});
							
// handler harvest button
$('body').on('click','.harvest',function(){

                                 $('.loading').removeClass('hide');

								$id=$(this).attr('id');
                                $botType=$id.substring(0,9);
                                 $botId=$id.substring(8, ($id.length));

                                if ($id.charAt(7)=="S")
                                    {
                                        $botType="S";
                                    }
                                else
                                    {
                                        $botType="W";
                                    }


                                $.ajax(
                                    {
                                        type: "POST",
                                        url: harvestPath,
                                        data: { botId : $botId, botType: $botType },
                                        cache: false,
                                        success: function ($resp)
                                        {
                                                $datas=$resp.split(";");  // 0: playlistId    1: newLastHarvestDate    2: botName
                                                 $playListId=$datas[0];

                                               if ($botType=="S")
                                                {
                                                    $botInfosContainer= "#selectBot" + $botId;
                                                    $($botInfosContainer).html('<td>'+$datas[2]+'</td><td>' + $datas[1]+'</td><td>0</td><td><img class="delBot" src="'+deleteImgPath+'" /></td>');
                                                    $newVidsContainer= "#botNewVids" + $botId;
                                                }else
                                                {
                                                    $botInfosContainer= "#selectWatchBot" + $botId;
                                                    $($botInfosContainer).html('<td>'+$datas[2]+'</td><td>' + $datas[1]+'</td><td>0</td><td><img class="delBot" src="'+deleteImgPath+'" /></td>');
                                                    $newVidsContainer= "#botNewWatchVids" + $botId;
                                                }

                                                $($newVidsContainer).html('<iframe id="ytplayer" type="text/html" width="320" height="195" src="http://www.youtube.com/embed?listType=playlist&list='+ $.trim($datas[0])+'" frameborder="0"/>');
                                                $($newVidsContainer).removeClass('hide');
                                            $('.loading').addClass('hide');
                                        }
                                    });

});


// handler select a channel in the createBot tab
$('body').on('click','.selectSubsForNewBot',function(){

    if ($(this).attr('src')==uncheckImgPath)
    {
        $(this).attr('src', checkImgPath);
    }else
    {
        $(this).attr('src', uncheckImgPath);
    }

    if ($(this).attr('id')=='all'){
        $newState=$(this).attr('src');
        $('.selectSubsForNewBot').each(function(){
           $(this).attr('src',$newState);
        });
    }
    else{
            $('#all').attr('src',uncheckImgPath);
    }
});

// handler delete BotChannel Button
$('body').on('click', '.delBotChan',function(){

    $('.loading').removeClass('hide');

        $chanId=$(this).attr('id');
        $botId=$(this).attr('name');

    $.ajax(
        {
            type: "POST",
            url: deleteBotChanPath,
            data: {chanId: $chanId, botId : $botId},
            cache: false,
            success: function ($confirm)
            {

                    $botchanToRemove='#'+$chanId+$botId;
                    $($botchanToRemove).remove();

                $('.loading').addClass('hide');
            }

        });

});

// handler delete bot button
$('body').on('click','.delBot',function(){

    $('.loading').removeClass('hide');

            $botToDel=$(this).parent().parent().attr('id').substring(9);


            $.ajax(
                {
                    type: "POST",
                    url: deleteBotPath,
                    data: {botId: $botToDel},
                    cache: false,
                    success: function ($confirm)
                    {

                        if ($confirm)
                        {
                            $botchansToRemove='#botChannels'+ $botId;
                            $botToRemove='#selectBot' + $botId;
                            $newVidContainerToRemove='#botNewVids'+ $botId;

                            $($newVidContainerToRemove).remove();
                            $($botchansToRemove).remove();
                            $($botToRemove).remove();
                        }

                        $('.loading').addClass('hide');
                    }
                });
});


// handler createBot button wiht selected subscriptions
$('body').on('click','#createBot',function(){

    $('.loading').removeClass('hide');

    $name=$('#createName').val();
    if ($name=='') $name='No Name';
    $first='true';
    $preListChanshtml='';
    $deleteImgPath="";
    $resp='';


    checkImgPath=checkImgPath.trim();

    $('.selectSubsForNewBot').each(function(){

        if ($(this).attr('src')==checkImgPath){
            if ($first=='false'){
                $resp=$resp+';'+$(this).attr('id')+';'+$(this).parent().parent().text();
               // $preListChanshtml+='<tr id="'+$(this).attr('id')+'"><td><div>'+$(this).parent().parent().text()+'</div></td><td><img class="delBotChan" id="'+$(this).attr('id')+'" src="'+deleteImgPath+'"/></td></tr>';
            }else{

                $resp=$(this).attr('id')+';'+$(this).parent().parent().text();
                $first='false';
             //   $preListChanshtml+='<tr><td><div>'+$(this).parent().parent().text()+'</div></td><td><img class="delBotChan" id="'+$(this).attr('id')+'" src="'+deleteImgPath+'"/></td></tr>';
            }
        }
    }
    );

    if ($resp!='')
        {
            $chansTab=$resp;

            $.ajax(
            {
                    type: "POST",
                    url: createBotPath,
                    data: {chansTab: $chansTab, name : $name},
                    cache: false,
                    success: function ($botId)
                    {

                        $('.selectSubsForNewBot').each(function(){

                                if ($(this).attr('src')==checkImgPath){
                                    if ($first=='false'){

                                        $preListChanshtml+='<tr id="'+$(this).attr('id')+$botId+'"><td><div>'+$(this).parent().parent().text()+'</div></td><td><img name="'+$botId+'" class="delBotChan" id="'+$(this).attr('id')+$botId+'" src="'+deleteImgPath+'"/></td></tr>';
                                    }else{


                                        $first='false';
                                        $preListChanshtml+='<tr><td><div>'+$(this).parent().parent().text()+'</div></td><td><img name="'+$botId+'" class="delBotChan" id="'+$(this).attr('id')+$botId+'" src="'+deleteImgPath+'"/></td></tr>';
                                    }
                                }
                            }
                        );


                        $listChansHtml='<div class="botChannels" id="botChannels'+ $.trim($botId)+'"><table class="botChans">'+$preListChanshtml+'</table></div>';
                        $newVidsHtml='<table class="botNewVids hide" id="botNewVids'+ $.trim($botId)+'"><tr><td><button class="btn btn-default" id="harvest' +$.trim($botId)+'" disabled="disabled">There is no new video</button></td><div id="createBotResp"></div></tr></table>';

                        $('#listBots').append('<tr class="selectBot" id="selectBot'+$.trim($botId)+'"><td>'+$name+'</td><td>just created</td><td>0</td><td><img class="delBot" src="'+ deleteImgPath+' " /></td></tr>');

                        $botChanContainer=$('#botsChannels');
                        $botChanContainer.append($listChansHtml);
                      // $botChanContainer.find('img').attr('name', $botId);


                        $botChansCont='#botChannels'+$botId;
                        $botNewVidsCont='#botNewVids'+$botId;
                        $selectBotToFocus='#selectBot'+$botId;


                        $('.botNewVids').addClass('hide');
                        $('.botChannels').addClass('hide');
                        $($botChansCont).removeClass('hide');
                        $($botNewVidsCont).removeClass('hide');
                        $('#vidPlayer').append($newVidsHtml);
                        $('.selectBot').css('background-color','#ffffff');
                        $($selectBotToFocus).css('background-color','#dddddd');



                        $('.loading').addClass('hide');
                     }
              });

           // reload_js(handlerJsPath);
           // $.ready();
        }
        else
        {
             alert ('no channel selected');
        }


});

// handler createBot button wiht selected subscriptions
$('body').on('click','#createWatchBot',function(){

    var $wordToWatch=$('#searchWord');

    if ($wordToWatch.val()!='')
    {

    $('.loading').removeClass('hide');

    $wordToSearch=$wordToWatch.val();

    $deleteImgPath="";
    $resp='';

    checkImgPath=checkImgPath.trim();

        $.ajax(
            {
                type: "POST",
                url: createWatchBotPath,
                data: {wordToWatch:$wordToWatch.val()},
                cache: false,
                success: function ($botId)
                {

                    $newVidsHtml='<table class="botNewVids hide" id="botNewWatchVids'+ $.trim($botId)+'"><tr><td><button class="btn btn-default" id="harvest' +$.trim($botId)+'" disabled="disabled">There is no new video</button></td><div id="createBotResp"></div></tr></table>';

                   // $('#listWatchers').append('<tr class="selectBot" id="selectWatchBot'+$.trim($botId)+'"><td>'+$name+'</td><td>just created</td><td>0</td><td><img class="delBot" src="'+ deleteImgPath+' " /></td></tr>');
                    $('#listWatchers').append('<tr class="selectBot" id="selectWatchBot'+$.trim($botId)+'"><td>'+$wordToSearch+'</td><td>just created</td><td>0</td><td><img class="delBot" src="'+ deleteImgPath+' " /></td></tr>');

                    $botNewVidsCont='#botNewWatchVids'+$botId;
                    $selectBotToFocus='#selectWatchBot'+$botId;

                    $('.botNewVids').addClass('hide');
                    $('.botNewWatchVids').addClass('hide');
                    $('.botChannels').addClass('hide');
                    $($botNewVidsCont).removeClass('hide');
                    $('#vidPlayer').append($newVidsHtml);
                    $('.selectBot').css('background-color','#ffffff');
                    $($selectBotToFocus).css('background-color','#dddddd');

                    $('.loading').addClass('hide');
                    alert('ok');
                }
            });

        // reload_js(handlerJsPath);
        // $.ready();
    }
    else
    {
        alert ('no channel selected');
    }


});


// handler selectBot Display
$('body').on('click','.selectBot',function (){

    var $botChansCont="";
    var $botNewVidsCont="";


    if ($(this).attr('id').substring(0,9)=="selectBot")
    {
        $botId= $(this).attr('id').substring(9);
        $botChansCont='#botChannels'+$botId;
        $botNewVidsCont='#botNewVids'+$botId;
    }
    else
    {
        $botId= $(this).attr('id').substring(14);
        $botNewVidsCont='#botNewWatchVids'+$botId;
    }

    $('.selectBot').css('background-color','#ffffff');
    $(this).css('background-color','#dddddd');


    $('.botNewVids').addClass('hide');

    $('.botChannels').addClass('hide');
    if ($botChansCont!="")
    {
        $($botChansCont).removeClass('hide');
    }
    $($botNewVidsCont).removeClass('hide');
});

/*
//handler createbot button
  $('#createbot').click(function  ()
							{

								$.get(
									'servicesPHP_SQL/BDDCreateBot.php',
									function ($code_html){
									
									$('#resultcreatebot').html($code_html);
									 $('#createBotContainer').html('<div>Main bot created <br> refresh to see the new bot</div><div>'+$code_html+'</div>');
									
									}									
								);
							}
  );
*/

function removeClass($id, $class)
{
    document.getElementById($id).className =document.getElementById($id.className.replace($class,''));
}



function reload_js(src) {
    $('script[src="' + src + '"]').remove();
    $('<script>').attr('src', src).appendTo('head');
}

