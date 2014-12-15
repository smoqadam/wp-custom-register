<?php ob_start();
/*
  Plugin Name: فرم ثبت نام ُ‌ لاگین و یادآوری رمزعبور
  Plugin URI: https://github.com/PHProir/wp-custom-register
  Description: به وسیله این پلاگین میتوانید فرمهای  :‌ ثبت نام ُ‌ لاگین و یادآوری رمزعبور را جدا از سیستم وردپرس داشته باشد. برای استفاده از این فرم ها کافیست یکی از ahortcode های زیر را در مکان مورد نظر خود قرار دهید ‌custom_login_shortcode, ‌custom_register_shortcode , custom_forgot_shortcode
  Version: 1.0
  Author: Saeed Moghadam <phpro.ir@gmail.com >
  Author URI: http://phpro.ir
*/

function registration_form( $username, $password, $email, $student_id, $first_name, $last_name, $national_code ) {
    echo '
    <style>
    div {
        margin-bottom:2px;
    }

    input{
        margin-bottom:4px;
    }
    </style>
    ';

    echo '
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
                نام کابری
            </span>
            <input class="form-control" type="text" name="username" value="' . ( isset( $_POST['username'] ) ? $username : null ) . '">
        </div>
    </div>

     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
رمز عبور
            </span>
    <input class="form-control" type="password" name="password" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '">
    </div>
    </div>

     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
ایمیل
            </span>
    <input type="text" class="form-control" name="email" value="' . ( isset( $_POST['email']) ? $email : null ) . '">
    </div>
    </div>

     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
شماره دانشجویی
            </span>
    <input type="text" class="form-control" name="student_id" value="' . ( isset( $_POST['student_id']) ? $student_id : null ) . '">
    </div>
    </div>

     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
کد ملی
            </span>
    <input type="text" class="form-control" name="national_code" value="' . ( isset( $_POST['national_code']) ? $national_code: null ) . '">
    </div>
    </div>


     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
نام
            </span>
    <input type="text" class="form-control" name="fname" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '">
    </div>
    </div>


     <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon">
نام خانوادگی
            </span>
    <input type="text" class="form-control" name="lname" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '">
    </div>
    </div>


    <input type="submit" name="submit" class="btn btn-success" value="ثبت نام"/>
    </form>
    ';
}


function register_validation($username, $password, $email, $student_id, $national_code){
    global $reg_errors;
    $reg_errors = new WP_Error;

    if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
        $reg_errors->add('field', 'لطفا فیلدهای  ضروری را  پر کنید');
    }

    if ( username_exists( $username ) )
        $reg_errors->add('user_name', 'متاسفانه این نام کاربری قبلا ثبت شده است');


    if ( ! validate_username( $username ) ) {
        $reg_errors->add( 'username_invalid', 'نام کابری وارد شده صحیح نیست' );
    }

    if ( 5 > strlen( $password ) ) {
        $reg_errors->add( 'password', 'رمز عبور باید بیشتر از ۵ حرف باشد' );
    }

    if ( !is_email( $email ) ) {
        $reg_errors->add( 'email_invalid', 'ایمیل را درست وارد کنید' );
    }

    if ( email_exists( $email ) ) {
        $reg_errors->add( 'email', 'این ایمیل قبلا ثبت شده است' );
    }


    if ( strlen( $student_id) < 1) {
        $reg_errors->add( 'student_id', 'اشماره دانشجویی را باید وارد کنید' );
    }

    if ( strlen( $national_code) < 1) {
        $reg_errors->add( 'national_code', 'اکد ملی خود را حتما وارد کنید' );
    }



    if ( is_wp_error( $reg_errors ) ) {

        if(count($reg_errors->get_error_messages())) {
            echo '<div class="alert alert-danger">';
            foreach ($reg_errors->get_error_messages() as $error) {
                echo '<strong>خطا</strong>:';
                echo $error . '<br/>';
            }
            echo '</div>';
        }

    }


}



function complete_registration() {
    global $reg_errors, $username, $password, $email, $student_id, $first_name, $last_name, $national_code;
    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
        $userdata = array(
            'user_login'    =>   $username,
            'user_email'    =>   $email,
            'user_pass'     =>   $password,
            'first_name'    =>   $first_name,
            'last_name'     =>   $last_name,
        );
        $user = wp_insert_user( $userdata );
        update_user_meta($user , 'student_id',$student_id);
        update_user_meta($user , 'national_code',$national_code);
        echo '<div class="alert alert-success">';
            echo 'ثبت نام شما با موفقیت انجام شد';
            echo '<a href='.site_url().'>';
                echo ' وارد سایت شوید ';
            echo '</a>';
        echo '</div>';

    }
}


function custom_registration_function() {
    if ( isset($_POST['submit'] ) ) {
        register_validation(
            $_POST['username'],
            $_POST['password'],
            $_POST['email'],
            $_POST['student_id'],
            $_POST['national_code']
        );

        // sanitize user form input
        global $username, $password, $email, $student_id, $first_name, $last_name, $national_code;
        $username       =   sanitize_user( $_POST['username'] );
        $password       =   esc_attr( $_POST['password'] );
        $email          =   sanitize_email( $_POST['email'] );
        $student_id     =   sanitize_text_field( $_POST['student_id'] );
        $first_name     =   sanitize_text_field( $_POST['fname'] );
        $last_name      =   sanitize_text_field( $_POST['lname'] );
        $national_code  =   sanitize_text_field( $_POST['national_code'] );
        $bio            =   esc_textarea( $_POST['bio'] );

        // call @function complete_registration to create the user
        // only when no WP_error is found
        complete_registration(
            $username,
            $password,
            $email,
            $student_id,
            $first_name,
            $last_name,
            $national_code
        );
    }

    registration_form(
        $username,
        $password,
        $email,
        $student_id,
        $first_name,
        $last_name,
        $national_code
    );
}



add_shortcode( 'custom_register_shortcode', 'custom_register_shortcode' );

function custom_register_shortcode() {
    ob_start();
    custom_registration_function();
    return ob_get_clean();
}



function custom_login_form() {

    if(isset($_GET['code'])){
        $code = $_GET['code'];
        $msg = '';
        switch($code){
            case 1 :
                $msg = 'برای استفاده از امکانات سایت باید وارد شوید. اگر عضو نیستید ';
                $msg .= '<a href="'.site_url().'/register" >ثبت نام کنید </a>';
                break;
        }
        echo '<div class="alert alert-danger">
                '.$msg.'
             </div>
        ';
    }
    echo '<form method="post" action="">
        <div class="login-form">
            <div class="form-group">
                <input name="login_name" type="text" class="form-control login-field" value="" placeholder="Username" id="login-name" />
                <label class="login-field-icon fui-user" for="login-name"></label>
            </div>

            <div class="form-group">
                <input  name="login_password" type="password" class="form-control login-field" value="" placeholder="Password" id="login-pass" />
                <label class="login-field-icon fui-lock" for="login-pass"></label>
            </div>
            <input class="btn btn-primary btn-lg btn-block" type="submit"  name="dlf_submit" value="Log in" />
    </form>
    ';
}

function custom_login_auth( $username, $password ) {
    global $user;
    $creds = array();
    $creds['user_login'] = $username;
    $creds['user_password'] =  $password;
    $creds['remember'] = true;
    $user = wp_signon( $creds, false );
    if ( is_wp_error($user) ) {
        echo $user->get_error_message();
    }
    if ( !is_wp_error($user) ) {
        wp_redirect( home_url() ); exit;
    }
}

function custom_login_process() {

    if (isset($_POST['login_password'])) {
        custom_login_auth($_POST['login_name'], $_POST['login_password']);
    }

    custom_login_form();
}

function custom_login_shortcode() {
    ob_start();
    custom_login_process();
    return ob_get_clean();
}

add_shortcode('custom_login_shortcode', 'custom_login_shortcode');








function custom_forgot_form(){
    ?>
        <form action="" class="form" method="post">
            <div class="form-group">
                <label for="username">نام کاربری</label>
                <input type="text" class="form-control" name="username" id="username" />
            </div>

            <div class="form-group">
                <label for="email">ایمیل</label>
                <input type="text" class="form-control" name="email" id="email" />
            </div>
            <input type="submit" class="btn btn-success" value="بازیابی رمز عبور" />
        </form>
    <?php
}


function custom_forgot_proccess($username,$email){
    if(empty($username)){
        $error[] = 'نام کاربری نباید خالی باشد';
    }
    if(empty($email)){
        $error[] = 'ایمیل نباید خالی باشد';
    }

    if(!is_email($email)){
        $error[] = 'ایمیل وارد شده صحیح نیست';
    }

    if(!email_exists($email)){
        $error[] = 'چنین ایمیلی ثبت نشده است.';
    }

    if(count($error)){
        echo '<div class="alert alert-danger">';
        echo implode('<br />',$error);
        echo '</div>';
        return;
    }


    $password = wp_generate_password(12,false);
    $user = get_user_by('email',$email);
    $user_update = wp_update_user(array(
        'ID'=>$user->ID,
        'user_pass'=>$password
    ));

    if($user_update){
        $to = $email;
        $subject = '';
        $from = get_option('name');
         $msg = '';

        $headers[] = 'MIME-Version: 1.0' . "\r\n";
        $headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers[] = "X-Mailer: PHP \r\n";
        $headers[] = 'From: '.$from.' < '.$email.'>' . "\r\n";

        $mail = wp_mail($to ,  $subject ,$msg , $headers);
        if($mail){
            echo '<div class="alert alert-success">
                    ایمیل خودرا برای رمز عبور جدید چک کنید. در صورت موجود نبودن در inbox لطفا اسپم را نیز چک کنید
                    </div>
            ';
        }else{
            echo '<div class="alert alert-danger">
                    متاسفانه خطایی رخ داده است. اطفا با مدیر سایت تماس بگیرید
                    </div>';
        }
    }

}


add_shortcode('custom_forgot_shortcode','custom_forgot_form_shortcode');
function custom_forgot_form_shortcode(){

    if(isset($_POST['email'])){
        custom_forgot_proccess($_POST['username'],$_POST['email']);

    }
    ob_start();
    custom_forgot_form();
    return ob_get_clean();
}