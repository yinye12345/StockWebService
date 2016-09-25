<meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<style>
 .ul-scrollable{margin-top:-15px !important;margin-right:-15px !important;margin-left:15px !important;  overflow-y: scroll; overflow-x:hidden; height: inherit;	}
 .btnTest{	width:100%;	}
</style>
<script type="text/javascript">
	$(function(){
		//var postUrl=<?php //echo '"'.Yii::$app->request->hostInfo.Yii::$app->request->url.'"';?>;
		var postUrl="";
		//样式绑定
		//var ch=document.body.clientHeight;
		var ch=window.screen.height;
		$(".panel-body").css("height",ch-250);
		$(".bar-api li").bind("click",function(){	$(".bar-api li").removeClass("active");	$(this).attr("class","active");	});
		//点击Controller
		$(".controller").on("click",function(){
			$(this).parents("ul").find("li").attr("class","");
			var controllerp=$(this).attr("_controller");
			$.ajax({
				type: "POST",
				url:postUrl,
				data:{	controller:controllerp, type:"controller"	},
				dataType:"json",
				success:function(data){
					var mHtml=new Array();
					$.each(data.methodList,function(key,value){
						mHtml.push('<li class="mothed" ><a href="#" _controller="'+controllerp+'">'+ value +'</a></li><li class="divider"></li>');
					});
					$(".methodList").empty().html(mHtml.join("\n"));
				}
			});
		});
		//点击Method
		$(document).on("click",".mothed",function(){
			var controllerp=$(this).find("a").attr("_controller");
			var methodp=$(this).find("a").html();
			$.ajax({
				type: "POST",
				url:postUrl,
				data:{	controller:controllerp, method:methodp, type:"mothed"	},
				dataType:"json",
				success:function(data){
					$(".methodName").html(methodp);
					$("#textForm").attr("action",<?php echo '"'.$postHost.'"'; ?>+"/"+controllerp+"/"+methodp).attr("enctype","application/x-www-form-urlencoded");
					$(".methodUrl").val(data.url);
					$(".methodDoc").html(data.doc);
					var html = new Array();
					for(key in data.parameter){
						var param=data.parameter[key].replace("$","");
						if(param!="msg"&&param!="doneVerify"&&param!='signature'&&param!="uploadImg"){
							if(param != "file"){
								html.push('<tr><td class="tdTitle">'+param+':</td><td><div class="input-group"><input  type="text" name="'+param+'" class="form-control" style="width:620px;margin-top:10px;margin-bottom:10px;"/></div></td></tr>');
							}else{
								$("#textForm").attr("enctype","multipart/form-data");
								html.push('<tr><td class="tdTitle">'+param+':</td><td><div class="input-group"><input  type="file" name="'+param+'" class="form-control" style="width:620px;margin-top:10px;margin-bottom:10px;"/></div></td></tr>');
							}
						}else{
							$("#textForm").attr("enctype","multipart/form-data");
							html.push('<tr><td class="tdTitle">'+param+':</td><td><div class="input-group"><input  type="file" name="'+param+'" class="form-control" style="width:620px;margin-top:10px;margin-bottom:10px;" /></div></td></tr>');
						}
					}
					if(methodp=="CheckAccess"){
						html.push('<tr><td></td><td><input type="submit"  value="提交"  class="btn btn-default btnCA" /></td></tr>');
					}else{
						html.push('<tr><td></td><td><input type="submit"  value="提交"  class="btn btn-default btnTest" /></td></tr>');
					}
					
					$("#testTable").html(html.join(' '));
				}
			});
		});

		$(document).on("click",".btn-default",function(){
			var url = $(".methodUrl").val();
			$("#textForm").attr("action",url);
		});
	});
</script>

<div>
	<nav class="navbar navbar-inverse navbar-fixed-top">
	  <div class="container-fluid">
	    <!-- Brand and toggle get grouped for better mobile display -->
	    <div class="navbar-header">
	      <span class="navbar-brand">推送平台</span>
	    </div>
	    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	      <ul class="nav navbar-nav">
      		<?php $isFirst=true;foreach ($apiList as  $key=>$item){?>
				<li <?php	if($isFirst) echo 'class="active"'; ?>><a href="#" _controller="<?php echo $key; ?>" class="controller"><?php echo $item;	?></a></li>
			<?php $isFirst=false;		}?>
	      </ul>
	    </div><!-- /.navbar-collapse -->
	  </div><!-- /.container-fluid -->
	</nav>
		<div class="col-xs-2 conent">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title" >接口列表名</h3>
					</div>
					<div class="panel-body">
						<ul class="nav nav-list ul-scrollable methodList">
							<?php foreach ($defaultMethod as  $item){?>
								<li class="mothed" ><a href="#" _controller="app"><?php echo $item;	?></a></li><li class="divider"></li>
							<?php	}?>
						</ul>
					</div>
				</div>
		</div>
		<div class="col-xs-10 conent">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title" >当前选中接口说明</h3>
					</div>
					<div class="panel-body " >
							<div class="testDiv">
							<table>
								<tr>
									<td class="tdTitle">方法名：</td>
									<td class="methodName">Login</td>
								</tr>
								<tr>
									<td class="tdTitle">调用地址：</td>
									<td>
										<div class="input-group">
											<input type="text" class="methodUrl form-control" style="width:720px; margin-bottom:10px;" value="<?php echo $defaultUrl; ?>"  />
										</div>
										<!-- <input type="button" value="复制" class="btnCopy"/> -->
									</td>
								</tr>
								<tr>
									<td class="tdTitle">注释说明：</td>
									<td>
										<div class="alert alert-info methodDoc" role="alert">
									        <?php echo str_replace("\n", "<br/>", $defaultDoc); ?>
									    </div>
										<!-- <textArea class="methodDoc form-control" cols="100" rows="15"  readonly><?php echo $defaultDoc; ?></textArea> -->
									</td>
								</tr>
								<tr>
									<td class="tdTitle">模拟调用：</td>
									<td>
										<div style="width:720px;margin-left:2px;border:1px solid #CCCCCC;" class="form-group">
										<form action="<?php echo $defaultUrl;?>"  method="post"  target="_blank"  id="textForm"  >
											<input type="hidden"  name="sign" id="sign"  />
											<table id="testTable">
												<!--<tr><td class="tdTitle">username:</td>
												<td>
													<div class="input-group">
													<input  type="text" name="username"  class="form-control" style="width:620px;margin-top:10px;margin-bottom:10px;" />
													</div>
												</td></tr>
												<tr><td class="tdTitle">password:</td>
												<td>
													<div class="input-group">
														<input  type="text" name="password" class="form-control" style="width:620px;margin-bottom:10px;" />
													</div>
												</td></tr>
												<tr>
													<td class="tdTitle">version:</td>
													<td>
													<div class="input-group">
													<input type="text" name="version" class="form-control" style="width:620px;margin-top:10px;margin-bottom:10px;">
													</div>
													</td>
												</tr>-->
												<tr><td></td><td><button type="submit" class="btn btn-default btnTest" >提交</button></td></tr>
											</table>
										</form>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
		</div>
</div>