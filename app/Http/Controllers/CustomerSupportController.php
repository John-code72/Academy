<?php
namespace App\Http\Controllers;

use App\Models\FileUploader;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketFaq;
use App\Models\TicketMacro;
use App\Models\TicketMessage;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CustomerSupportController extends Controller
{

    public function customer_support_ticket_index()
    {
        $defaultStatusIds = TicketStatus::where('default_view', 1)->pluck('id');

        $query = Ticket::whereIn('status_id', $defaultStatusIds);

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('subject', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['tickets'] = $query->paginate(10);
        return view('admin.customer_support.ticket.index', $page_data);
    }

    public function customer_support_ticket_create()
    {
        return view('admin.customer_support.ticket.create');
    }

    public function customer_support_ticket_store(Request $request)
    {

        $ticket['subject']     = $request->subject;
        $ticket['code']        = random_int(1000, 9999);
        $ticket['creator_id']  = $request->creator_id;
        $ticket['user_id']     = $request->user_id;
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
        $message['sender_id']          = $request->creator_id;
        $message['receiver_id']        = $request->user_id;

        TicketMessage::create($message);

        return redirect()->route('admin.customer.support.ticket.message', $insert_info->code)->with('success', 'Added successfully');
    }

    public function customer_support_ticket_edit($id)
    {
        $page_data['ticket'] = Ticket::where('id', $id)->first();
        return view('admin.customer_support.ticket.edit', $page_data);

    }

    public function customer_support_ticket_update(Request $request, $id)
    {

        $ticket = Ticket::find($id);

        if (! $ticket) {
            Session::flash('error', get_phrase('Ticket not found.'));
            return redirect()->back();
        }

        // Update ticket info
        $ticket->update([
            'subject'     => $request->subject,
            'category_id' => $request->category_id,
            'user_id'     => $request->user_id,
            'status_id'   => $request->status_id,
            'priority_id' => $request->priority_id,
        ]);

        // Update related messages
        TicketMessage::where('ticket_thread_code', $ticket->code)->update([
            'receiver_id' => $request->user_id,
        ]);

        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_delete($id)
    {

        $ticket = Ticket::find($id);

        // Check if ticket exists
        if (! $ticket) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }

        // Get all related messages before deleting them
        $ticketMessages = TicketMessage::where('ticket_thread_code', $ticket->code)->get();

        // Remove attached files if any
        foreach ($ticketMessages as $message) {
            if (! empty($message->file)) {
                remove_file($message->file);
            }
        }

        // Now delete the messages
        TicketMessage::where('ticket_thread_code', $ticket->code)->delete();

        // Then delete the ticket itself
        $ticket->delete();
        Session::flash('success', get_phrase('Ticket delete successfully'));
        return redirect()->back();

    }

    public function customer_support_ticket_message($ticket_thread_code = '')
    {

        $page_data['macros']         = TicketMacro::get();
        $page_data['ticket_details'] = Ticket::where('code', $ticket_thread_code)->first();
        $page_data['messages']       = TicketMessage::where('ticket_thread_code', $ticket_thread_code)->get();

        return view('admin.customer_support.ticket.ticket_details', $page_data);
    }

    public function customer_support_ticket_message_store(Request $request)
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

        $ticket['subject']     = $request->subject;
        $ticket['user_id']     = $request->user_id;
        $ticket['status_id']   = $request->status_id;
        $ticket['priority_id'] = $request->priority_id;
        $ticket['category_id'] = $request->category_id;

        Ticket::where('id', $request->ticket_id)->update($ticket);

        Session::flash('success', get_phrase('Message sent'));
        return redirect()->back();
    }

    public function customer_support_ticket_faq_index()
    {
        $query = TicketFaq::query();
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('question', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['faqs'] = $query->paginate(10);
        return view('admin.customer_support.faq.index', $page_data);
    }

    public function customer_support_ticket_faq_create()
    {
        return view('admin.customer_support.faq.create');
    }

    public function customer_support_ticket_faq_store(Request $request)
    {
        $data["question"] = $request->question;
        $data["answer"]   = $request->answer;

        TicketFaq::insert($data);
        Session::flash('success', get_phrase('Added successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_faq_edit($id)
    {
        $page_data['faq'] = TicketFaq::where('id', $id)->first();
        return view('admin.customer_support.faq.edit', $page_data);
    }

    public function customer_support_ticket_faq_update(Request $request, $id)
    {
        $data['question'] = $request->question;
        $data['answer']   = $request->answer;

        TicketFaq::where('id', $request->id)->update($data);
        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_faq_delete($id)
    {
        $query = TicketFaq::where("id", $id);
        if ($query->doesntExist()) {
            Session::flash('success', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $query->delete();
        Session::flash('success', get_phrase('FAQ delete successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_report()
    {
        $page_data['total_tickets'] = Ticket::get();
        $page_data['ticket_cards']  = TicketStatus::where('status', 1)->get();

        $page_data['user_tickets'] = Ticket::select('user_id', DB::raw('COUNT(*) as total_tickets'))
            ->groupBy('user_id')->with('user')->orderByDesc('total_tickets')->limit(10)->get();

        $page_data['barchart'] = collect(range(1, 12))->map(function ($month) {
            $monthName = Carbon::create()->month($month)->format('M');

            return [
                'title'  => $monthName,
                'ticket' => Ticket::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', $month)
                    ->count(),
            ];
        });

        return view('admin.customer_support.report.index', $page_data);
    }

    public function customer_support_ticket_macro_index()
    {
        $query = TicketMacro::query();
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('title', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['ticket_macros'] = $query->paginate(10);
        return view('admin.customer_support.macro.index', $page_data);
    }

    public function customer_support_ticket_macro_create()
    {
        return view('admin.customer_support.macro.create');
    }

    public function customer_support_ticket_macro_store(Request $request)
    {
        $data["title"]       = $request->title;
        $data["description"] = $request->description;

        TicketMacro::insert($data);
        Session::flash('success', get_phrase('Added successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_macro_edit($id)
    {
        $page_data['macro'] = TicketMacro::where('id', $id)->first();
        return view('admin.customer_support.macro.edit', $page_data);
    }

    public function customer_support_ticket_macro_update(Request $request, $id)
    {
        $data['title']       = $request->title;
        $data['description'] = $request->description;

        TicketMacro::where('id', $request->id)->update($data);
        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_macro_delete($id)
    {
        $query = TicketMacro::where("id", $id);
        if ($query->doesntExist()) {
            Session::flash('success', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $query->delete();
        Session::flash('success', get_phrase('Macro delete successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_category_index()
    {
        $query = TicketCategory::query();
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('title', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['ticket_categories'] = $query->paginate(10);
        return view('admin.customer_support.category.index', $page_data);
    }
    public function customer_support_ticket_category_create()
    {
        return view('admin.customer_support.category.create');
    }

    public function customer_support_ticket_category_store(Request $request)
    {
        $data["title"]  = $request->title;
        $data["status"] = $request->status;

        TicketCategory::insert($data);
        Session::flash('success', get_phrase('Added successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_category_edit($id)
    {
        $page_data['category'] = TicketCategory::where('id', $id)->first();
        return view('admin.customer_support.category.edit', $page_data);
    }

    public function customer_support_ticket_category_update(Request $request, $id)
    {
        $data['title']  = $request->title;
        $data['status'] = $request->status;

        TicketCategory::where('id', $request->id)->update($data);
        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_category_delete($id)
    {
        $query = TicketCategory::where("id", $id);
        if ($query->doesntExist()) {
            Session::flash('success', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $query->delete();
        Session::flash('success', get_phrase('Category delete successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_priority_index()
    {
        $query = TicketPriority::query();
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('title', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['ticket_priorities'] = $query->paginate(10);
        return view('admin.customer_support.priority.index', $page_data);
    }
    public function customer_support_ticket_priority_create()
    {
        return view('admin.customer_support.priority.create');
    }

    public function customer_support_ticket_priority_store(Request $request)
    {
        $data["title"]  = $request->title;
        $data["status"] = $request->status;

        TicketPriority::insert($data);
        Session::flash('success', get_phrase('Added successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_priority_edit($id)
    {
        $page_data['priority'] = TicketPriority::where('id', $id)->first();
        return view('admin.customer_support.priority.edit', $page_data);
    }

    public function customer_support_ticket_priority_update(Request $request, $id)
    {
        $data['title']  = $request->title;
        $data['status'] = $request->status;

        TicketPriority::where('id', $request->id)->update($data);
        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_priority_delete($id)
    {
        $query = TicketPriority::where("id", $id);
        if ($query->doesntExist()) {
            Session::flash('success', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $query->delete();
        Session::flash('success', get_phrase('Priority delete successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_status_index()
    {
        $query = TicketStatus::query();
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $query = $query->where('title', 'LIKE', '%' . $_GET['search'] . '%');
        }
        $page_data['ticket_statuses'] = $query->paginate(10);
        return view('admin.customer_support.status.index', $page_data);
    }
    public function customer_support_ticket_status_create()
    {
        return view('admin.customer_support.status.create');
    }
    public function customer_support_ticket_status_store(Request $request)
    {

        if (isset($request->icon) && $request->hasFile('icon')) {
            $path = "uploads/ticket_files/" . nice_file_name($request->title, $request->icon->extension());
            FileUploader::upload($request->icon, $path, 400, null, 200, 200);
            $data['icon'] = $path;
        }

        $data["title"]        = $request->title;
        $data["status"]       = $request->status;
        $data['default_view'] = $request->default_view;
        $data['color']        = $request->color;

        TicketStatus::insert($data);
        Session::flash('success', get_phrase('Added successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_status_edit($id)
    {
        $page_data['status'] = TicketStatus::where('id', $id)->first();
        return view('admin.customer_support.status.edit', $page_data);
    }

    public function customer_support_ticket_status_update(Request $request, $id)
    {

        if (isset($request->icon) && $request->hasFile('icon')) {
            remove_file(TicketStatus::where('id', $id)->first()->icon);
            $path = "uploads/ticket_files/" . nice_file_name($request->title, $request->icon->extension());
            FileUploader::upload($request->icon, $path, 400, null, 200, 200);
            $data['icon'] = $path;
        }

        $data['title']        = $request->title;
        $data['status']       = $request->status;
        $data['default_view'] = $request->default_view;
        $data['color']        = $request->color;

        TicketStatus::where('id', $request->id)->update($data);
        Session::flash('success', get_phrase('Updated successfully'));
        return redirect()->back();
    }

    public function customer_support_ticket_status_delete($id)
    {
        $query = TicketStatus::where("id", $id);
        if ($query->doesntExist()) {
            Session::flash('success', get_phrase('Data not found.'));
            return redirect()->back();
        }

        remove_file($query->first()->icon);
        $query->delete();
        Session::flash('success', get_phrase('Status delete successfully'));
        return redirect()->back();
    }
}
