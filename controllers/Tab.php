<?php namespace Mavitm\Compon\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Mavitm\compon\Models\Mtmdata;
use Backend;

class Tab extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];

    //public $listConfig = 'config_list.yaml';
    public $listConfig = [
        'list' => 'config_list.yaml',
        'subList' => 'config_sub_list.yaml'
    ];
    public $formConfig = 'config_form.yaml';

    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'mavitm.compon.access_tab'
    ];

    public function __construct()
    {

        $this->vars['parentlist'] = true;

        parent::__construct();

        BackendMenu::setContext('Mavitm.Compon', 'main-menu-item', 'tab_menu');
    }


    public function listExtendQuery($query)
    {

        if(in_array($this->action, ["sublist", "reorder"])){
            $query->where([
                'group' => 'tab',
                'parent_id' => $this->params[0]
            ])->orderBy("sort_order","asc");
        }else{
            $query->where([
                'group' => 'tab',
                'parent_id' => 0
            ]);
        }
    }

    public function reorderExtendQuery($query)
    {
        $query->where([
            'group' => 'tab',
            'parent_id' => $this->params[0]
        ]);
    }

    public function formExtendFields($form)
    {
        if(!empty($this->params[0])) {
            if ($this->params[0] == "subcreate" || ($this->action == "update" && !empty($this->params[1]))) {
                $this->vars['parentlist'] = false;

                $form->addFields([
                    'parent_id' => [
                        'type' => 'dropdown',
                        'label' => 'mavitm.compon::lang.compon.parent_id',
                        'default' => $this->params[1]
                    ],
                    'group' => [
                        'label' => 'mavitm.compon::lang.compon.group',
                        'span' => 'right',
                        'type' => 'dropdown',
                        'default' => 'tab'
                    ],
                ]);

            }
        }

        if($this->action == "create"){
            $form->addFields([
                'group' => [
                    'label' => 'mavitm.compon::lang.compon.group',
                    'span' => 'right',
                    'type' => 'dropdown',
                    'required' => 1,
                    'default' => 'tab'
                ],
            ]);
        }

    }

    public function create()
    {
        if(!empty($this->params[0])) {
            if ($this->params[0] == "subcreate") {
                if (!empty($this->params[1])) {
                    $parent = Mtmdata::where("id", $this->params[1])->first();
                    $this->pageTitle = $parent->title . ' - ' . e(trans('mavitm.compon::lang.tab.newItem'));
                }
            }
        }
        $this->asExtension('FormController')->create();
    }

    public function update()
    {

        if( !empty($this->params[1]) )
        {
            if(!empty($this->params[1])){
                $parent = Mtmdata::where("id",$this->params[1])->first();
                $this->pageTitle = $parent->title.' - '.e(trans('mavitm.compon::lang.tab.edit'));
            }
        }

        $this->asExtension('FormController')->update($this->params[0]);
    }

    public function sublist()
    {
        if(empty($this->params[0])){
            return redirect()->to(Backend::url('mavitm/compon/tab'));
        }

        $parent = Mtmdata::where("id",$this->params[0])->first();
        $this->pageTitle = $parent->title;
        $this->vars['parentlist'] = false;
        $this->vars['parent'] = $parent;
        $this->asExtension('ListController')->index();
    }



}