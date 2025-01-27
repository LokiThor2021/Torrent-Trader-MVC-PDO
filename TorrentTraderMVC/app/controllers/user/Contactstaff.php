<?php
class Contactstaff
{
    public function __construct()
    {
        $this->session = Auth::user(0, 1);
    }

    public function index()
    {
        $data = [
            'title' => 'Contact Staff',
        ];
        View::render('contactstaff/index', $data, 'user');
    }

    public function submit()
    {
        if (Input::get("msg") && Input::get("sub")) {
            (new Captcha)->response($_POST['g-recaptcha-response']);
            $msg = Input::get("msg");
            $sub = Input::get("sub");
            $error_msg = "";
            if (!$msg || !$sub) {
                $error_msg = Lang::T("NO_EMPTY_FIELDS");
            }
            if ($error_msg != "") {
                Redirect::autolink(URLROOT, $error_msg);
            } else {
                $added = TimeDate::get_date_time();
                $userid = Users::has('id') ?? 0;
                $req = Staffmessage::insertStaffMessage($userid, $added, $msg, $sub);
                if ($req == 1) {
                    Redirect::autolink(URLROOT, Lang::T("CONTACT_SENT"));
                } else {
                    Redirect::autolink(URLROOT, Lang::T("TRYLATER"));
                }
            }
        }
    }

}