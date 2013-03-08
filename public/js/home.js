/**
 * 
 */
$(document).ready(function() { 
	
    var options = { 
    	target:        '#fileinfo'   // target element(s) to be updated with server response         
    };
    $('#myForm').submit(function() { 
    	$("#fileinfo").html('<center><strong>图片上传中.....</strong></center>');
    	$(this).ajaxSubmit(options);
    	return false; 
    });
    var gallery = {
    		target:        '#gallery_result',
    		success:		function reload(){parent.location.reload();}
    };
    $('#gallery_name').submit(function() {
    	$(this).ajaxSubmit(gallery);
    	return false; 
    });
    var del = {
    		target:        '#del_result',
    		success:		function successreload(){parent.location.reload();}
    };

    $('#del_gallery_form').submit(function() { 
    	$(this).ajaxSubmit(del);
    	return false; 
    });

    var password = {
    		target:        '#edit_userinfo_result'
    };
    $('#edit_userinfo_form').submit(function() {
    	$(this).ajaxSubmit(password);
    	return false; 
    });
    
    $('#del_gallery').modal({
    	backdrop:true,
    	keyboard:true,
    	show:false
    });

    $('#myModal').modal({
    	backdrop:true,
    	keyboard:true,
    	show:false
    });
    $('#add_gallery').modal({
    	backdrop:true,
    	keyboard:true,
    	show:false
    });
    $('#open_image').modal({
    	backdrop:true,
    	keyboard:true,
    	show:false
    });
    $('#edit_userinfo').modal({
    	backdrop:true,
    	keyboard:true,
    	show:false
    });


    $("button[name='open_picture']").click(function(event) {
    	$("#return_img_value_body").html('');
    	$("#return_img_value_body_tip").empty();
    	$("#return_img_value_body").html('');
    	var picture = $(this).attr("value");
    	picture=eval('('+picture+')');  
			$.post("/home/getpictureinfo/", 
					{picture_id : picture.id}, 
					function(short_url)
					{
				//		$("#return_img_value_body").html(data);
						$("#return_img_value_header_name").html(picture.name);
						$("#return_img_value_header_time").html("上传于："+picture.time);
						$("#return_img_value_body").html('<img src="'+picture.url+'">');
						var footer = '<a class="btn btn-warning pull-left" href="javascript:delete_picture('+picture.fid+')"><span>删除图片</span></a>';
						footer += '<input value="'+short_url+'">';
				//		footer += '<a id="copy" class="btn btn-primary" href="javascript:clipInit()"><span>复制地址</span></a>';
				//		footer += '<a name="copy" class="btn btn-primary" href="javascript:copyIntoClipboard()"><span>复制地址</span></a>';
						footer += '<button class="btn btn-danger cancel" data-dismiss="modal"><i class="icon-remove icon-white"></i><span>关闭窗口</span></button>'
						$("#return_img_value_footer").html(footer);
					},
					"json"
			);
	});
    
    ZeroClipboard.setMoviePath('/public/js/ZeroClipboard.swf');
    clipInit();
   
});


function send_active_mail()
{
	$.ajax({
		type: "POST",
    	url: "/auth/activemail",
    	success: function(r){
   			$("#return_value").html(r);
      		$("#return_value").addClass("alert alert-block alert-success");
    	}
    }); 
}
function check_active_mail()
{
	$.ajax({
		type: "POST",
	    url: "/auth/checkvolume",
	    success: function(r){
	    	if(r == 1)
	    	{
	    		$("#return_value").html('<strong>账户激活成功！</strong>');
		    	$("#return_value").addClass("alert alert-block alert-success");
		    	jump(3); 
		    	
	    	}else {
	    		$("#return_value").html(r);
		    	$("#return_value").addClass("alert alert-block alert-success");
	    	}
	    }
	}); 
}

function jump(count) {  
    window.setTimeout(function(){  
        count--;  
        if(count > 0) {  
        	$("#return_value").html('<strong>'+(count+1)+'&nbsp;秒后页面自动刷新！</strong>');
	    	$("#return_value").addClass("alert alert-block alert-success");
            jump(count);  
        } else {  
        	parent.location.reload();
        }  
    }, 1000);  
} 

function delete_picture(fid)
{
	$.ajax({
		type: "POST",
	    url: "/home/deletepicture",
	    data: {fid:fid},
	    success: function(r){
	    	if(r == 1)
	    	{
	    		$("#return_img_value_body_tip").html('<strong>图片删除成功！</strong>');
		    	$("#return_img_value_body_tip").addClass("alert alert-block alert-success");
		    	jump(1); 
		    	
	    	}else {
	    		$("#return_img_value_body_tip").html('<strong>图片删除失败！</strong>');
		    	$("#return_img_value_body_tip").addClass("alert alert-block alert-error");
	    	}
	    }
	}); 
}
function copy_img_url(id)
{
	$.ajax({
		type: "POST",
	    url: "/index/imgurl",
	    data: {id:id},
	    dataType:"json",
	    success: function(r){
	    	if(r.err_code == '0')
	    	{
	    		$("#return_img_value_body_tip").html('<center><strong>复制地址成功！</strong></center>');
		    	$("#return_img_value_body_tip").addClass("alert alert-block alert-success");
		    	
	    	}else {
	    		$("#return_img_value_body_tip").html('<center><strong>复制地址失败！</strong></center>');
		    	$("#return_img_value_body_tip").addClass("alert alert-block alert-error");
	    	} 
	    }
	});
}

function logout()
{
	if(confirm('您真的要退出问君图床吗？'))
	{
		$.ajax({
			type: "POST",
		    url: "/auth/logout",
		    success: function(r){
		    	window.opener=null;
				window.open('','_self');
				window.close();
		    }
		});
	}
}


function clipInit() 
{
	
    var clip = new ZeroClipboard.Client();
    clip.setHandCursor(true);
    clip.setText('wenjun.in');
    clip.glue('copy');
}
