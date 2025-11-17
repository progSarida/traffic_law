<?php
include("common/header.php");
include("common/menu.php");
?>
<!-- Simple login form -->
	<form action="class/lib.php" method="post">
		<div class="panel panel-body login-form">
			<div class="text-center">
				<div class="icon-object border-slate-300 text-slate-300"><i class="icon-reading"></i></div>
				<h5 class="content-group">Login to your account</h5>
			</div>

			<div class="form-group has-feedback has-feedback-left">
				<input type="text" name="loginAttr[]" class="form-control" placeholder="Username" required >
				<div class="form-control-feedback">
					<i class="icon-user text-muted"></i>
				</div>
			</div>

			<div class="form-group has-feedback has-feedback-left">
				<input type="password" name="loginAttr[]" class="form-control" placeholder="Password" required="">
				<div class="form-control-feedback">
					<i class="icon-lock2 text-muted"></i>
				</div>
			</div>

			<div class="form-group">
				<button type="submit" name="login" class="btn btn-primary btn-block">Sign in <i class="icon-circle-right2 position-right"></i></button>
			</div>

			<!-- <div class="text-center">
				<a href="login_password_recover.html">Forgot password?</a>
			</div> -->
		</div>
	</form>
<!-- /simple login form -->
<?php
include("common/footer.php");
?>