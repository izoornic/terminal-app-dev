<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Models\TiketKomentar;
//use Illuminate\Support\Facades\Mail;
//use Illuminate\Mail;
use App\Mail\NotyfyMail;

use App\Actions\Bankomati\BankomatTiketMailingActions;

class SendEmailController extends Controller
{
     //
     public $emaildata;
     public $komentari;
     public function index()
     {
          $mail_action = new BankomatTiketMailingActions(36);
          $mail_action->sendEmails("novi");
          $this->emaildata = [
               'tiketlink' => 'https://google.com',
               'hedaing' => 'Tiket',
               'name' => 'Izornic', 
               'email' => 'izornic@gmail.com',
               'row1' => "Izornic",
               'row2' => "Izornic",
               'row3' => "Izornic",
               'row4' => "Izornic",
               'row5' => "Izornic",
               'row6' => "Izornic",
               'row7' => "Izornic",
               'row8' => "Izornic",
               'row9' => "Izornic",
               'row10' => "Izornic",
               'row11' => "Izornic",
          ];

          $this->komentari = TiketKomentar::where('tiketId', '=', 1215)
                                             ->leftJoin('users', 'users.id', '=', 'tiket_komentars.korisnikId')                         
                                             ->get();
               
          
          return view('emails.demoMail', ['data' => $this->emaildata, 'komentari' => $this->komentari]);
          //Mail::to('izornic@gmail.com')->send(new NotyfyMail());

          /* if (Mail::failures()) {
           return response()->Fail('Sorry! Please try again latter');
      }else{
           return response()->success('Great! Successfully send in your mail');
         } */
     }

     public static function sendMail()
     {

          Mail::to('izornic@gmail.com')->send(new NotyfyMail());

          if (Mail::failures()) {
               return response()->Fail('Sorry! Please try again latter');
          } else {
               return response()->success('Great! Successfully send in your mail');
          }
     }
}
