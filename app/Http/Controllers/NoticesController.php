<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrepareNoticeRequest;
use App\Provider;
use App\Notice;
use App\User;

class NoticesController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
        
        parent::__construct();
    }
    
    public function index() {
        $notices = $this->user->notices;
        
        return view('notices.index', compact('notices'));
    }
    
    public function create() {
        // get list of providers
        $providers = Provider::lists('name', 'id');
        
        // load a view to create a new notice
        return view('notices.create', compact('providers'));
    }
    
    public function confirm(PrepareNoticeRequest $request) {
        
        $template = $this->compileDmcaTemplate($data = $request->all());
        
        session()->flash('dmca', $data);
        
        return view('notices.confirm', compact('template'));
    }
    
    public function compileDmcaTemplate($data) {
        $data = $data + [
          'name' => $this->user->name,
          'email' => $this->user->email
        ];
        
        return view()->file(app_path('Http/Templates/dmca.blade.php'), $data);
    }
    
    public function store(Request $request) {
       
       $notice = $this->createNotice($request);
       
       // fire off email
       \Mail::queue(['text' => 'emails.dmca'], compact('notice'), function($message) use ($notice) {
           $message->from($notice->getOwnerEmail())
                    ->to($notice->getRecipientEmail())
                    ->subject('DMCA Notice');
       });
       
       flash('Your DMCA notice has been delivered!');
       
       return redirect('notices');
    }
    
    public function update($noticeId, Request $request) {
        $isRemoved = $request->has('content_removed');
        
        Notice::findOrFail($noticeId)
            ->update(['content_removed' => $isRemoved]);
    }
    
    private function createNotice(Request $request) {
        $data = session()->get('dmca');
       
       $notice = Notice::open($data)
               ->useTemplate($request->input('template'));
       
       $this->user->notices()->save($notice);
       
       return $notice;
    }
}
