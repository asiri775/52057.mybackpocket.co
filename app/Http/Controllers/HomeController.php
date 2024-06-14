<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Modules\Contact\Emails\NotificationToHost;
use Modules\Hotel\Models\Hotel;
use Modules\Location\Models\LocationCategory;
use Modules\Page\Models\Page;
use Modules\News\Models\NewsCategory;
use Modules\News\Models\Tag;
use Modules\News\Models\News;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Contact\Models\Contact;
use Illuminate\Support\Facades\Mail;
use Modules\Contact\Emails\NotificationToAdmin;
use Modules\Space\Models\Space;
use App\Models\EmailSubject;
use App\Models\EmailTemplate;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $home_page_id = setting_item('home_page_id');
        $home_hotel_id = setting_item('home_hotel_id');

        if ($home_hotel_id && $row = Hotel::where("id", $home_hotel_id)->where("status", "publish")->first()) {
            $translation = $row->translateOrOrigin(app()->getLocale());
            $hotel_related = [];
            $location_id = $row->location_id;
            if (!empty($location_id)) {
                $hotel_related = Hotel::where('location_id', $location_id)->where("status", "publish")->take(4)->whereNotIn('id', [$row->id])->with(['location', 'translations', 'hasWishList'])->get();
            }
            $review_list = $row->getReviewList();
            $data = [
                'row' => $row,
                'translation' => $translation,
                'hotel_related' => $hotel_related,
                'location_category' => LocationCategory::where("status", "publish")->with('location_category_translations')->get(),
                'booking_data' => $row->getBookingData(),
                'review_list' => $review_list,
                'seo_meta' => $row->getSeoMetaWithTranslation(app()->getLocale(), $translation),
                'body_class' => 'is_single'
            ];
            $this->setActiveMenu($row);
            return view('Hotel::frontend.detail', $data);
        }
        if ($home_page_id && $page = Page::where("id", $home_page_id)->where("status", "publish")->first()) {

            $this->setActiveMenu($page);

            $translation = $page->translateOrOrigin(app()->getLocale());
            $seo_meta = $page->getSeoMetaWithTranslation(app()->getLocale(), $translation);
            $seo_meta['full_url'] = url("/");
            $seo_meta['is_homepage'] = true;
            $data = [
                'row' => $page,
                "seo_meta" => $seo_meta,
                'translation' => $translation
            ];
            return view('Page::frontend.detail', $data);
        }
        $model_News = News::where("status", "publish");
        $data = [
            'rows' => $model_News->paginate(5),
            'model_category' => NewsCategory::where("status", "publish"),
            'model_tag' => Tag::query(),
            'model_news' => News::where("status", "publish"),
            'breadcrumbs' => [
                ['name' => __('News'), 'url' => url("/news"), 'class' => 'active'],
            ],
            "seo_meta" => News::getSeoMetaForPageList()
        ];
        return view('News::frontend.index', $data);
    }

    public function checkConnectDatabase(Request $request)
    {
        $connection = $request->input('database_connection');
        config([
            'database' => [
                'default' => $connection . "_check",
                'connections' => [
                    $connection . "_check" => [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ],
                ],
            ],
        ]);

        try {
            DB::connection()->getPdo();
            $check = DB::table('information_schema.tables')->where("table_schema", "performance_schema")->get();
            if (empty($check) and $check->count() == 0) {
                return $this->sendSuccess(false, __("Access denied for user!. Please check your configuration."));
            }
            if (DB::connection()->getDatabaseName()) {
                return $this->sendSuccess(false, __("Yes! Successfully connected to the DB: " . DB::connection()->getDatabaseName()));
            } else {
                return $this->sendSuccess(false, __("Could not find the database. Please check your configuration."));
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function paymentError()
    {
        return view('errors.payment_error');
    }

    public function contactSubmit(Request $request)
    {
        $row = new Contact($request->input());
        $row->message = $request->input('notes');
        $row->status = 'sent';
        if ($row->save()) {
            $this->sendEmail($row);
            return redirect()->back()->with('success', 'Contact request has been sent');
        } else {
            die("not saved");
        }
    }

    public function contactHost(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255'
            ],
            'phone' => [
                'required',
                'string',
                'max:255'
            ],
            'message' => [
                'required',
                'string'
            ],
            'space' => ['required'],
        ];
        $messages = [
            'name.required' => __('Name is required field'),
            'phone.required' => __('Phone is required field'),
            'message.required' => __('Message is required field'),
            'email.required' => __('Email is required field'),
            'space.required' => __('Space is required field'),
            'email.email' => __('Email invalidate'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        } else {
            $name = $request->get('name');
            $phone = $request->get('phone');
            $email = $request->get('email');
            $message = $request->get('message');
            $space = $request->get('space');

            $space = Space::where('id', $space)->first();
            $user = User::where('id', $space->create_user)->first();

            try {
               
                $EmailSubject = EmailSubject::where('token', 'sds616hk')->first();
                $template = EmailTemplate::where('domain', 9)->where('subject_id', $EmailSubject['id'])->first();

                Mail::to($user->email)->send(new NotificationToHost($name, $email, $phone, $message, $space,$template));
            } catch (Exception $exception) {
                dd($exception);
                Log::warning("Contact Send Mail: " . $exception->getMessage());
            }

            return $this->sendSuccess(__('Contact request sent successfully'));
        }
    }

    protected function sendEmail($contact)
    {
        if ($admin_email = setting_item('admin_email')) {
            try {
                Mail::to($admin_email)->send(new NotificationToAdmin($contact));
            } catch (Exception $exception) {
                Log::warning("Contact Send Mail: " . $exception->getMessage());
            }
        }
    }

    public function howWorksHost()
    {
        return view('site.how-works-host');
    }

    public function makeItCount()
    {
        return view('site.make-count');
    }

}
