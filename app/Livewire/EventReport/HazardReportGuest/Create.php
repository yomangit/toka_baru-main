<?php

namespace App\Livewire\EventReport\HazardReportGuest;

use DateTime;
use App\Models\User;
use Livewire\Component;
use App\Models\Division;
use App\Models\Eventsubtype;
use App\Models\HazardReport;
use Livewire\WithPagination;
use App\Models\LocationEvent;
use Livewire\WithFileUploads;
use App\Models\choseEventType;
use App\Models\WorkflowDetail;
use App\Models\TypeEventReport;
use App\Models\EventUserSecurity;
use App\Notifications\toModerator;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class Create extends Component
{
    use WithFileUploads;
    use WithPagination;
    public $location_name, $search, $location_id, $divider = 'Input Hazard Report', $TableRisk = [], $Event_type = [], $RiskAssessment = [], $EventSubType = [], $ResponsibleRole, $division_id, $parent_Company, $business_unit, $dept, $workflow_template_id;
    public $searchLikelihood                               = '', $searchConsequence                               = '', $tablerisk_id, $risk_assessment_id, $workflow_detail_id, $reference, $select_divisi;
    public $risk_likelihood_id, $risk_likelihood_notes;
    public $risk_consequence_id, $risk_consequence_doc, $risk_probability_doc, $show = false;
    public $workgroup_id, $workgroup_name, $show_immidiate                           = 'yes';
    public $search_workgroup                                                         = '', $divisi_search                                                         = '', $search_report_by                                                         = '', $search_report_to                                                         = '', $fileUpload, $location_search                                                         = '';
    public $event_type_id, $sub_event_type_id, $report_by, $report_byName, $report_by_nolist, $report_to, $report_toName, $report_to_nolist, $date, $event_location_id, $site_id, $company_involved, $task_being_done, $documentation, $description, $immediate_corrective_action, $suggested_corrective_action, $preliminary_cause, $corrective_action_suggested;
    public $dropdownLocation                                                                         = 'dropdown', $hidden                                                                         = 'block';
    public $dropdownWorkgroup                                                                        = 'dropdown', $hiddenWorkgroup                                                                        = 'block';
    public $dropdownReportBy                                                                         = 'dropdown', $hiddenReportBy                                                                         = 'block';
    public $dropdownReportTo                                                                         = 'dropdown', $hiddenReportTo                                                                         = 'block';
    public $alamat, $kondisi_tidak_aman, $tindakan_tidak_aman, $tindakkan_selanjutnya, $showLocation = false;
    public $data                                                                                     = [];

    // data action
    public function mount()
    {

        if (Auth::check()) {
            $reportBy            = (Auth::user()->lookup_name) ? Auth::user()->lookup_name : Auth::user()->name;
            $this->report_byName = $reportBy;
            $this->report_by     = Auth::user()->id;
        }
    }
    public function rules()
    {
        if ($this->show_immidiate === 'yes') {
            return [
                'workgroup_name'              => ['required'],
                'event_type_id'               => ['required'],
                'sub_event_type_id'           => ['required'],
                'report_byName'               => ['required'],
                'date'                        => ['required'],
                'tindakkan_selanjutnya'       => ['required'],
                'documentation'               => 'nullable|mimes:jpg,jpeg,png,svg,gif,xlsx,pdf,docx',
                'description'                 => ['required'],
                'immediate_corrective_action' => ['required'],
                'location_name'               => ['required'],
                'location_id'                 => ['required'],
            ];
        } else {
            return [
                'workgroup_name'        => ['required'],
                'event_type_id'         => ['required'],
                'sub_event_type_id'     => ['required'],
                'report_byName'         => ['required'],
                'date'                  => ['required'],
                'documentation'         => 'nullable|mimes:jpg,jpeg,png,svg,gif,xlsx,pdf,docx',
                'description'           => ['required'],
                'location_id'           => ['required'],
                'location_name'         => ['required'],
                'tindakkan_selanjutnya' => ['required'],
            ];
        }
    }
    public function messages()
    {
        return [
            'event_type_id.required'               => 'kolom wajib di isi',
            'tindakkan_selanjutnya.required'       => 'kolom wajib di centang',
            'report_byName.required'               => 'kolom wajib di isi',
            'workgroup_name.required'              => 'kolom wajib di isi',
            'date.required'                        => 'kolom wajib di isi',
            'site_id.required'                     => 'kolom wajib di isi',
            'documentation.mimes'                  => 'hanya format jpg,jpeg,png,svg,gif,xlsx,pdf,docx file types are allowed',
            'documentation.nullable'               => 'kolom wajib di isi',
            'description.required'                 => 'kolom wajib di isi',
            'immediate_corrective_action.required' => 'kolom wajib di isi',
            'location_name.required'               => 'kolom wajib di isi',
            'location_id.required'                 => 'kolom wajib di isi',
            'workgroup_name.required'              => 'kolom wajib di isi',
        ];
    }
    public function reportedBy($id)
    {
        $this->report_by        = $id;
        $ReportBy               = User::whereId($id)->first();
        $this->report_byName    = $ReportBy->lookup_name;
        $this->report_by_nolist = null;
        $this->hiddenReportBy   = 'hidden';
    }
    public function reportedTo($id)
    {
        $this->report_to        = $id;
        $ReportTo               = User::whereId($id)->first();
        $this->report_toName    = $ReportTo->lookup_name;
        $this->report_to_nolist = null;
        $this->hiddenReportTo   = 'hidden';
    }
    public function ReportByAndReportTo()
    {
        if (! empty($this->report_by_nolist)) {
            $this->report_by     = null;
            $this->report_byName = $this->report_by_nolist;
        }
    }
    public function select_division($id)
    {
        $this->division_id     = $id;
        $this->hiddenWorkgroup = 'hidden';
        $this->hiddenReportBy  = 'hidden';
    }
    public function clickReportBy()
    {
        $this->dropdownReportBy = 'dropdown dropdown-open dropdown-end';
        $this->hiddenReportBy   = 'block';
    }
    public function clickReportTo()
    {
        $this->hiddenReportTo = 'block';
    }
    public function clickWorkgroup()
    {
        $this->dropdownWorkgroup = 'dropdown dropdown-open dropdown-end';
        $this->hiddenWorkgroup   = 'block';
    }
    public function changeConditionDivision()
    {
        $this->business_unit = null;
        $this->dept          = null;
        $this->select_divisi = null;
        $this->division_id   = null;
    }
    public function realTimeFunc()
    {
        // Tampilkan lokasi jika dipilih
        $this->showLocation = !empty($this->location_id);

        // Ambil event_type berdasarkan route
        $routePath = Request::getPathInfo();
        if (choseEventType::where('route_name', 'LIKE', $routePath)->exists()) {
            $eventTypeIds = choseEventType::where('route_name', 'LIKE', $routePath)
                ->pluck('event_type_id');
            $this->Event_type = TypeEventReport::whereIn('id', $eventTypeIds)->get();
        }

        // Ambil subtype jika event_type_id dipilih
        $this->EventSubType = $this->event_type_id
            ? Eventsubtype::where('event_type_id', $this->event_type_id)->get()
            : [];

        // Ambil ekstensi file dokumentasi
        if ($this->documentation) {
            $this->fileUpload = pathinfo($this->documentation->getClientOriginalName(), PATHINFO_EXTENSION);
        }

        // Tampilkan form jika user adalah superadmin (id = 1)
        $this->show = Auth::check() && Auth::user()->role_user_permit_id == 1;

        // Proses data divisi
        if ($this->division_id) {
            $divisi = Division::with([
                'DeptByBU.BusinesUnit.Company',
                'DeptByBU.Department',
                'Company',
                'Section'
            ])->find($this->division_id);

            if ($divisi) {
                $company = optional($divisi->DeptByBU->BusinesUnit->Company)->name_company;
                $department = optional($divisi->DeptByBU->Department)->department_name;
                $section = optional($divisi->Section)->name;
                $companySelf = optional($divisi->Company)->name_company;

                $this->workgroup_name = implode('-', array_filter([
                    $company,
                    $department,
                    $companySelf,
                    $section,
                ]));

                $this->divisi_search = Division::with([
                    'DeptByBU.BusinesUnit.Company',
                    'DeptByBU.Department',
                    'Company',
                    'Section'
                ])
                    ->where('id', $this->division_id)
                    ->searchParent(trim($this->parent_Company))
                    ->searchBU(trim($this->business_unit))
                    ->searchDept(trim($this->dept))
                    ->searchComp(trim($this->select_divisi))
                    ->orderBy('dept_by_business_unit_id', 'asc')
                    ->get();
            }
        } else {
            // Jika tidak ada division_id
            $this->divisi_search = Division::with([
                'DeptByBU.BusinesUnit.Company',
                'DeptByBU.Department',
                'Company',
                'Section'
            ])
                ->searchDeptCom(trim($this->workgroup_name))
                ->searchParent(trim($this->parent_Company))
                ->searchBU(trim($this->business_unit))
                ->searchDept(trim($this->dept))
                ->searchComp(trim($this->select_divisi))
                ->orderBy('dept_by_business_unit_id', 'asc')
                ->get();
        }

        // Ambil workflow detail jika ada
        if ($this->workflow_template_id && WorkflowDetail::where('workflow_administration_id', $this->workflow_template_id)->exists()) {
            $workflow = WorkflowDetail::where('workflow_administration_id', $this->workflow_template_id)->first();
            $this->workflow_detail_id = optional($workflow)->id;
            $this->ResponsibleRole = optional($workflow)->responsible_role_id;
        }
    }
    public function render()
    {
        $this->realTimeFunc();
        $this->ReportByAndReportTo();
        return view('livewire.event-report.hazard-report-guest.create', [
            'Report_By'  => User::searchNama(trim($this->report_byName))->paginate(100, ['*'], 'Report_By'),
            'Report_To'  => User::searchNama(trim($this->report_toName))->paginate(100, ['*'], 'Report_To'),
            'Division'   => $this->divisi_search,
            'EventType'  => $this->Event_type,
            'Location'   => LocationEvent::all(),
        ])
            ->extends('base.index', [
                'header' => 'Hazard Report',
                'title'  => 'Hazard Report',
            ])
            ->section('content');
    }
    public function store()
    {
        $hazard          = HazardReport::exists();
        $referenceHazard = "TT–OHS–HZD-";
        if (! $hazard) {
            $reference       = 1;
            $references      = str_pad($reference, 4, "0", STR_PAD_LEFT);
            $this->reference = $referenceHazard . $references;
        } else {
            $hazard          = HazardReport::latest()->first();
            $reference       = $hazard->id + 1;
            $references      = str_pad($reference, 4, "0", STR_PAD_LEFT);
            $this->reference = $referenceHazard . $references;
        }
         $this->validate();
        if (! empty($this->documentation)) {
            $file_name        = $this->documentation->getClientOriginalName();
            $this->fileUpload = pathinfo($file_name, PATHINFO_EXTENSION);
            $this->documentation->storeAs('public/documents/hzd', $file_name);
        } else {
            $file_name = "";
        }
        if ($this->show_immidiate === 'no') {
            $this->immediate_corrective_action = null;
        }
        if ($this->tindakkan_selanjutnya == 0) {
            $WorkflowDetail           = WorkflowDetail::where('workflow_administration_id', $this->workflow_template_id)->where('name', 'like', '%' . "closed" . '%')->first();
            $this->workflow_detail_id = $WorkflowDetail->id;
            $closed_by                = $this->report_byName;
        } else {
            $closed_by = '';
        }
        $filds = [
            'event_type_id'               => $this->event_type_id,
            'sub_event_type_id'           => $this->sub_event_type_id,
            'reference'                   => $this->reference,
            'report_by'                   => $this->report_by,
            'report_to'                   => $this->report_to,
            'division_id'                 => $this->division_id,
            'date'                        => DateTime::createFromFormat('d-m-Y : H:i', $this->date)->format('Y-m-d : H:i'),
            'location_name'               => $this->location_name,
            'event_location_id'           => $this->location_id,
            'site_id'                     => $this->site_id,
            'show_immidiate'              => $this->show_immidiate,
            'kondisi_tidak_aman'          => $this->kondisi_tidak_aman,
            'tindakan_tidak_aman'         => $this->tindakan_tidak_aman,
            'tindakkan_selanjutnya'       => $this->tindakkan_selanjutnya,
            'company_involved'            => $this->company_involved,
            'risk_consequence_id'         => $this->risk_consequence_id,
            'risk_likelihood_id'          => $this->risk_likelihood_id,
            'workgroup_name'              => $this->workgroup_name,
            'report_byName'               => $this->report_byName,
            'report_toName'               => $this->report_toName,
            'task_being_done'             => $this->task_being_done,
            'documentation'               => $file_name,
            'description'                 => $this->description,
            'immediate_corrective_action' => $this->immediate_corrective_action,
            'suggested_corrective_action' => $this->suggested_corrective_action,
            'corrective_action_suggested' => $this->corrective_action_suggested,
            'report_by_nolist'            => $this->report_by_nolist,
            'report_to_nolist'            => $this->report_to_nolist,
            'workflow_detail_id'          => $this->workflow_detail_id,
            'workflow_template_id'        => $this->workflow_template_id,
            'closed_by'                   => $closed_by,
        ];
        $HazardReport = HazardReport::create($filds);
        $this->dispatch(
            'alert',
            [
                'text'            => "Laporan Hazard Anda Sudah Terkirim, Terima kasih sudah melapor!!!",
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->dispatch('buttonClicked', [
            'duration' => 4000,
        ]);
        // Notification
        $getModerator = (Auth::check() ? EventUserSecurity::where('responsible_role_id', $this->ResponsibleRole)->where('type_event_report_id', $this->event_type_id)->where('user_id', 'NOT LIKE', Auth::user()->id)->pluck('user_id')->toArray() : EventUserSecurity::where('responsible_role_id', $this->ResponsibleRole)->where('user_id', 'NOT LIKE', Auth::user()->id)->pluck('user_id')->pluck('user_id')->toArray());
        $User         = User::whereIn('id', $getModerator)->get();
        $url          = $HazardReport->id;
        foreach ($User as $key => $value) {
            $users     = User::whereId($value->id)->get();
            $offerData = [
                'greeting'  => 'Hi' . ' ' . $value->lookup_name,
                'subject'   => 'Hazard Report' . ' ' . $this->task_being_done,
                'line'      => $this->report_byName . ' ' . 'has submitted a hazard report, please review',
                'line2'     => 'by click the button below',
                'line3'     => 'Thank you',
                'actionUrl' => url("/eventReport/hazardReportDetail/$url"),
            ];
            Notification::send($users, new toModerator($offerData));
        }
        $report_to = User::where('id', $this->report_to)->whereNotNull('email')->get();
        if ($report_to) {
            $offerData = [
                'greeting'  => 'Hi' . ' ' . $this->report_toName,
                'subject'   => 'hazard report with reference number ' . ' ' . $this->reference,
                'line'      => $this->report_byName . ' ' . 'has sent a hazard report to you, please review it',
                'line2'     => 'by click the button below',
                'line3'     => 'Thank you',
                'actionUrl' => url("/eventReport/hazardReportDetail/$url"),
            ];
            Notification::send($report_to, new toModerator($offerData));
            $this->clearFields();
            // $this->redirectRoute('hazardReportCreate', ['workflow_template_id' => $this->workflow_template_id]);
        }
    }

    public function clearFields()
    {
        $this->report_byName               = "";
        $this->report_toName               = "";
        $this->workgroup_name              = "";
        $this->division_id                 = "";
        $this->date                        = "";
        $this->documentation               = "";
        $this->description                 = "";
        $this->immediate_corrective_action = "";
        $this->location_name               = "";
        $this->location_id                 = "";
        $this->kondisi_tidak_aman          = "";
        $this->tindakan_tidak_aman         = "";
        $this->tindakkan_selanjutnya         = "";
        $this->workgroup_name              = "";
    }
}
