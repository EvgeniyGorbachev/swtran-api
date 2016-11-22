<?php
namespace App;
use Schema;

trait QueryBuilderByParamsTrait {
    protected $constantParameters = [
        'sort',
    ];
    
    protected $tables = [];
    
    public function useParams ($query, $request, $tableName) {
        $this->tables = $tableName;

        $sortField = $request->input('sort');
        $params = $request->all();
       
        //sorting | ?sort=-userName -> sort desc, sort=userName -> sort asc
        if ($sortField && $sortField != 'null') {
            $order = $sortField{0};
            if ($order == '-') {
                $sortField = substr($sortField, 1);
                $query->orderBy($sortField, 'desc');
            } else {
                $query->orderBy($sortField, 'asc');
            }
        }

        //search by param | ?userName=Zhenya -> use LIKE operator %Zhneya%
        foreach ($params as $paramName => $paramValue) {
            if (!$this->isConstantParameters($paramName) && $paramValue != 'null') {
                $query->where($this->getFullFieldName($paramName), 'LIKE', '%' . $paramValue . '%');
            }
        }
        
        return $query;
    }
    
    protected function getFullFieldName($field) {
        foreach ($this->tables as $tableName) {
            return Schema::hasColumn($tableName, $field) ? $tableName . '.' . $field: $field;
        }
    }

    protected function isConstantParameters($param) {
        return in_array($param, $this->constantParameters);
    }
}