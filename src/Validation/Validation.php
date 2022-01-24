<?php
namespace Pulse\Validation;

class Validation
{

    protected $fields = [];
    protected $rules = [];
    protected $rulesObj;
    protected $defaultMessage;
    protected $errorList = [];

    protected $mainValidation = ['not_required'];

    public function __construct(array $fields) {
        $this->fields = $fields;
        $this->rulesObj = new Rules();
        $this->defaultMessage = new ValidationMessage();
    }

    public function SetRules(array $rules)
    {
        foreach ($rules as $field => $value) {
            if( ! in_array($field, $this->fields)){
                throw new \Exception("($field) is not defined as a field");
            }
            if(! isset($value['terms']) || empty($value['terms'])){
                continue;
            }
            $this->rules[$field] =[];
            $terms = explode("|", $value['terms']);
            foreach ($terms as $termV) {
                $reg = "/^(?P<name>[a-zA-Z_][a-zA-Z_\w]*)(?:\((?P<params>[\d\-,a-z A-Z_.]+)\))?$/";
                preg_match($reg, $termV, $results);

                if(! isset($results['name'])){
                    throw new \Exception("$termV is not a valid validtion syntax");
                }
                if( ! method_exists($this->rulesObj, $results['name']) 
                    && ! in_array($results['name'],$this->mainValidation)){
                    throw new \Exception("$termV is not a valid validtion");
                }

                $tmp['name'] = $results['name'];
                if(isset($results['params'])){
                    $tmp['params'] = explode(',', $results['params']);
                }else{
                    $tmp['params'] = [];
                }
                $error_msg = $this->defaultMessage->get($results['name']);

                //rename the field name if exists
                $field_name = $field;
                if(isset($value['field_name'])){
                    $field_name = $value['field_name'];
                }
                $tmp['error'] = $this->defaultMessage->ReplaceData($error_msg, $field_name, $tmp['params']);

                $this->rules[$field][] = $tmp;
            }
            foreach ($value['errors']??[] as $key => $value) {
               foreach ($this->rules[$field] as $key_ => $value_) {
                   if($value_['name'] == $key){
                        $this->rules[$field][$key_]['error'] = $value;
                        break;
                   }
               }
            }
        }
    }

    public function Validate(array $data)
    {
        $this->errorList = [];//reset the error list
       foreach ($data as $key => $value) {
           if(isset($this->rules[$key])){
                foreach ($this->rules[$key] as $i => $body) {
                    if(in_array($body['name'],$this->mainValidation)){
                        if($body['name'] =='not_required'){
                            if(empty($value)) break;
                        }
                    }else{
                        $params = array_merge([$value], $body['params']);
                        $res = call_user_func_array(
                            array($this->rulesObj, $body['name']),
                            $params
                        );
                    }

                    if( ! $res){
                        $this->errorList[] = $body['error'];
                        break;
                    }
                }
           }
       }
       return count($this->errorList) == 0;
    }

    public function getErrorList()
    {
        return $this->errorList;
    }

    public function setErrorToList(string $error_msg)
    {
        $this->errorList[] = $error_msg;
    }
}
