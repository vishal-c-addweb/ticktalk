<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ClientsDataTable extends BaseDataTable
{

    private $viewClientPermission;
    private $editClientPermission;
    private $deleteClientPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewClientPermission = user()->permission('view_clients');
        $this->editClientPermission = user()->permission('edit_clients');
        $this->deleteClientPermission = user()->permission('delete_clients');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {

        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('clients.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editClientPermission == 'all' || ($this->editClientPermission == 'added' && user()->id == $row->added_by) || ($this->editClientPermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('clients.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteClientPermission == 'all' || ($this->deleteClientPermission == 'added' && user()->id == $row->added_by) || ($this->deleteClientPermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('client_name', function ($row) {
                return ucfirst($row->name);
            })
            ->editColumn(
                'name',
                function ($row) {
                    return view('components.client', [
                        'user' => $row
                    ]);
                }
            )
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->format($this->global->date_format);
                }
            )
            ->editColumn(
                'status',
                function ($row) {
                    if ($row->status == 'active') {
                        return ' <i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
                    }
                    else {
                        return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
                    }
                }
            )
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['name', 'action', 'status', 'check']);
    }

    /**
     * @param User $model
     * @return User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();
        $users = $model->withoutGlobalScope('active')->with('session', 'clientDetails')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'client_details.company_name', 'users.email', 'users.image', 'users.created_at', 'users.status', 'client_details.added_by')
            ->where('roles.name', 'client');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $users = $users->where(DB::raw('DATE(users.`created_at`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
            $users = $users->where(DB::raw('DATE(users.`created_at`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && $request->status != '') {
            $users = $users->where('users.status', $request->status);
        }

        if ($request->client != 'all' && $request->client != '') {
            $users = $users->where('users.id', $request->client);
        }

        if (!is_null($request->category_id) && $request->category_id != 'all') {
            $users = $users->where('client_details.category_id', $request->category_id);
        }

        if (!is_null($request->sub_category_id) && $request->sub_category_id != 'all') {
            $users = $users->where('client_details.sub_category_id', $request->sub_category_id);
        }

        if (!is_null($request->project_id) && $request->project_id != 'all') {
            $users->whereHas('projects', function ($query)use($request) {
                return $query->where('id', $request->project_id);
            });
        }

        if (!is_null($request->contract_type_id) && $request->contract_type_id != 'all') {
            $users->whereHas('contracts', function ($query)use($request) {
                return $query->where('contracts.contract_type_id', $request->contract_type_id);
            });
        }

        if (!is_null($request->country_id) && $request->country_id != 'all') {
            $users->whereHas('country', function ($query)use($request) {
                return $query->where('id', $request->country_id);
            });
        }

        if ($this->viewClientPermission == 'added' || $this->viewClientPermission == 'both') {
            $users = $users->where('client_details.added_by', user()->id);
        }

        if ($request->searchText != '') {
            $users = $users->where(function ($query) {
                $query->where('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.email', 'like', '%' . request('searchText') . '%')
                    ->orWhere('client_details.company_name', 'like', '%' . request('searchText') . '%');
            });
        }

        return $users;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('clients-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2)
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            /* ->stateSave(true) */
            ->processing(true)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["clients-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('app.id') => ['data' => 'id', 'name' => 'id'],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false],
            __('app.customers')  => ['data' => 'client_name', 'name' => 'users.name', 'visible' => false],
            __('app.email') => ['data' => 'email', 'name' => 'email'],
            __('app.status') => ['data' => 'status', 'name' => 'status'],
            __('app.createdAt') => ['data' => 'created_at', 'name' => 'created_at'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'clients_' . date('YmdHis');
    }

    public function pdf()
    {
        set_time_limit(0);

        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
