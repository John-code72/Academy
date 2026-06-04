<?php
namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\FileUploader;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomerSupportController extends Controller
{

    public function support_ticket_index()
    {
        if (in_array(auth()->user()->role, ['student', 'instructor'])) {
            $page_data['tickets'] = Ticket::where('user_id', auth()->id())->paginate(10);
        } else {
            $page_data['tickets'] = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10); // admin hole empty result
        }

        $view_path = 'frontend.' . get_frontend_settings('theme') . '.student.customer_support.index';
        return view($view_path, $page_data);
    }

    public function support_ticket_create()
    {
        $view_path = 'frontend.' . get_frontend_settings('theme') . '.student.customer_support.create';
        return view($view_path);
    }

    public function support_ticket_store(Request $request)
    {

        $ticket['subject']     = $request->subject;
        $ticket['code']        = random_int(1000, 9999);
        $ticket['creator_id']  = $request->admin_id;
        $ticket['user_id']     = auth()->user()->id;
        $ticket['status_id']   = $request->status_id;
        $ticket['priority_id'] = $request->priority_id;
        $ticket['category_id'] = $request->category_id;

        $ticket_id = Ticket::insertGetId($ticket);

        $insert_info = Ticket::find($ticket_id);

        $paths = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $paths[] = FileUploader::upload($file, 'uploads/ticket_files');
            }
        }

        if (! empty($paths)) {
            $message['file'] = json_encode($paths);
        }

        $message['ticket_thread_code'] = $insert_info->code;
        $message['message']            = $request->message;
        $message['sender_id']          = auth()->user()->id;
        $message['receiver_id']        = $request->admin_id;

        TicketMessage::create($message);

        return redirect()->route('support.ticket.message', $insert_info->code)->with('success', 'Added successfully');
    }

    public function support_ticket_message($ticket_thread_code = '')
    {

        $page_data['ticket_details'] = Ticket::where('code', $ticket_thread_code)->first();
        $page_data['conversation']   = TicketMessage::where('ticket_thread_code', $ticket_thread_code)->get();

        $view_path = 'frontend.' . get_frontend_settings('theme') . '.student.customer_support.ticket_details';
        return view($view_path, $page_data);

    }

    public function support_ticket_message_store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message'            => $request->file('file') ? 'nullable|string' : 'required|string',
            'sender_id'          => 'required|integer|exists:App\Models\User,id',
            'receiver_id'        => 'required|integer|exists:App\Models\User,id',
            'ticket_thread_code' => 'required|integer',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'The message field is required');
        }
        $data = [
            'message'            => $request->message,
            'sender_id'          => $request->sender_id,
            'receiver_id'        => $request->receiver_id,
            'ticket_thread_code' => $request->ticket_thread_code,
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        $paths = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $paths[] = FileUploader::upload($file, 'uploads/ticket_files');
            }
        }

        if (! empty($paths)) {
            $data['file'] = json_encode($paths);
        }

        TicketMessage::insert($data);

        Session::flash('success', get_phrase('Message sent'));
        return redirect()->back();
    }

}
