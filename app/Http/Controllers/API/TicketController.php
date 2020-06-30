<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Mail;
use DB;

use App\User;
use App\Models\Ticket;
use App\Models\Log;

class TicketController extends Controller
{
    //
    public $successStatus = 200;
    public $invalidStatus = 201;


    /**
     * create tickert api
     *
     * @return \Illuminate\Http\Response
     */
    public function createTicket(Request $request)
    {
        if (Auth::user()->role == 1) {
            $validator = Validator::make($request->all(), [
                'ticket_name' => 'required',
                'ticket_price' => 'required',
                'ticket_desc' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);            
            }

            DB::beginTransaction();

            try {
                $input = $request->all();
                $ticket = new Ticket;
                $ticket->ticket_name = $input['ticket_name'];
                $ticket->ticket_currency = $input['ticket_currency'];
                $ticket->ticket_price = $input['ticket_price'];
                $ticket->ticket_desc = $input['ticket_desc'];
                $ticket->status = 0; // 0 -> request
                $ticket->req_status = false; // not request back status
                $ticket->created_by = $input['user_id'];
                $ticket->save();
                
                $log = new Log;
                $log->ticket_id = $ticket->id;
                $log->desc = 'create ticket';
                $log->by_person = Auth::user()->id;
                $log->created_by = Auth::user()->id;
                $log->save();

                $success['name'] =  $ticket->ticket_name;

                $mail = $this->mailSent('toSuper', $ticket->id, $input['email'], 'is created by me.');

                DB::commit();
                // all good

                return response()->json(['success'=>$success], $this->successStatus);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                $success['msg'] =  "Something is error";
                return response()->json(['success'=>$success], $this->invalidStatus);
            }
        } else {
            $success['msg'] =  "You can't do this action";
            return response()->json(['success'=>$success], $this->invalidStatus);
        }
    }


    /**
     * get tickert api
     *
     * @return \Illuminate\Http\Response
     */
    public function getTicket(Request $request)
    {
        $input = $request->all();
        $ticket = Ticket::where('id', $input['id'])->get();

        $success['ticket'] =  $ticket;

        return response()->json(['success'=>$success], $this->successStatus);
    }

    /**
     * request tickert info api
     *
     * @return \Illuminate\Http\Response
     */
    public function requestTicket(Request $request)
    {
        if (Auth::user()->role != 1) {
            $input = $request->all();

            DB::beginTransaction();

            try {
                $ticket = Ticket::where('id', $input['ticket_id'])->get();
                if ($ticket[0]->status == 1) { 
                    if (Auth::user()->role == 2) { // request back from supervisor
                        $ticket[0]->status = 0;
                    }
                    $ticket[0]->req_status = true; // request back status
                } else {
                    $ticket[0]->req_status = true; // request back status
                }
                $ticket[0]->save();

                $log = new Log;
                $log->ticket_id = $ticket[0]->id;
                $log->desc = 'request more information';
                $log->by_person = Auth::user()->id;
                $log->created_by = Auth::user()->id;
                $log->save();

                $success['ticket'] =  $ticket[0];

                if ($input['role'] == 2) {
                    $mail = $this->mailSent('toStaff', $input['ticket_id'], $input['email'], 'is need more information');
                } else {
                    $mail = $this->mailSent('toSuper', $input['ticket_id'], $input['email'], 'is need more information');
                }

                DB::commit();
                // all good

                return response()->json(['success'=>$success], $this->successStatus);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                $success['msg'] =  "Something is error";
                return response()->json(['success'=>$success], $this->invalidStatus);
            }
        } else {
            $success['msg'] =  "You can't do this action";
            return response()->json(['success'=>$success], $this->invalidStatus);
        }
    }

    /**
     * request tickert info api
     *
     * @return \Illuminate\Http\Response
     */
    public function approveTicket(Request $request)
    {
        if (Auth::user()->role != 1) {
            $input = $request->all();
            DB::beginTransaction();

            try {
                $ticket = Ticket::where('id', $input['ticket_id'])->get();
                if ($ticket[0]->status == 0) {
                    $ticket[0]->status = 1; // approve status from supervisor
                    $ticket[0]->req_status = false;
                } else if ($ticket[0]->status == 1) {
                    $ticket[0]->status = 2; // approve status from leader
                    $ticket[0]->req_status = false;
                }
                $ticket[0]->save();

                $log = new Log;
                $log->ticket_id = $ticket[0]->id;
                $log->desc = 'approve ticket';
                $log->by_person = Auth::user()->id;
                $log->created_by = Auth::user()->id;
                $log->save();

                $success['ticket'] =  $ticket[0];

                if ($input['role'] == 2) {
                    $mail = $this->mailSent('toLeader', $input['ticket_id'], $input['email'], 'is approved by me.');
                } else {
                    $mail = $this->mailSent('toStaff', $input['ticket_id'], $input['email'], 'is host now.');
                }

                DB::commit();
                // all good

                return response()->json(['success'=>$success], $this->successStatus);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                $success['msg'] =  "Something is error";
                return response()->json(['success'=>$success], $this->invalidStatus);
            }
        } else {
            $success['msg'] =  "You can't do this action";
            return response()->json(['success'=>$success], $this->invalidStatus);
        }
    }

    /**
     * request tickert info api
     *
     * @return \Illuminate\Http\Response
     */
    public function getTicketList(Request $request)
    {
        $input = $request->all();
        $tickets = Ticket::get();

        $success['tickets'] =  $tickets;

        return response()->json(['success'=>$success], $this->successStatus);
    }

   /**
     * request tickert info api
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTicket(Request $request)
    {
        if (Auth::user()->role == 1) {
            $validator = Validator::make($request->all(), [
                'ticket_name' => 'required',
                'ticket_price' => 'required',
                'ticket_desc' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);            
            }

            DB::beginTransaction();

            try {
                $input = $request->all();
                $ticket = Ticket::where('id', $input['ticket_id'])->get();
                $ticket[0]->ticket_name = $input['ticket_name'];
                $ticket[0]->ticket_currency = $input['ticket_currency'];
                $ticket[0]->ticket_price = $input['ticket_price'];
                $ticket[0]->ticket_desc = $input['ticket_desc'];
                $ticket[0]->req_status = false; // request back status
                $ticket[0]->save();

                $log = new Log;
                $log->ticket_id = $ticket[0]->id;
                $log->desc = 'update ticket information';
                $log->by_person = Auth::user()->id;
                $log->created_by = Auth::user()->id;
                $log->save();

                $success['ticket'] =  $ticket[0];

                $mail = $this->mailSent('toSuper', $input['ticket_id'], $input['email'], 'is updated by me.');

                DB::commit();
                // all good

                return response()->json(['success'=>$success], $this->successStatus);
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                $success['msg'] =  "Something is error";
                return response()->json(['success'=>$success], $this->invalidStatus);
            }
        } else {
            $success['msg'] =  "You can't do this action";
            return response()->json(['success'=>$success], $this->invalidStatus);
        }
    }

    /**
     * send notification mail
     *
     * @return \Illuminate\Http\Response
     */
    public function mailSent($type, $ticket_id, $fromEmail, $status)
    {
        if ($type == 'toSuper') {
            $user = User::where('role', 2)->get();
        } else if ($type == 'toLeader') {
            $user = User::where('role', 3)->get();

        } else {
            $user = User::where('role', 1)->get();
        }

        $ticket = Ticket::where('id', $ticket_id)->get();

        foreach($user as $value) {
            $to_name = $value['name'];
            $to_email = $value['email'];
            $data['name'] = "Ticket App Admin";
            $data['ticket'] = $ticket;
            $data['status'] = $status;
            $data['from'] = $fromEmail;

            Mail::send('mail', $data, function($message) use ($to_name, $to_email, $fromEmail) {
                $message->to($to_email, $to_name)->subject('Ticket Notification');
                $message->from($fromEmail,'Notification Mail');
            });
        }

        return "success";
    }
}
