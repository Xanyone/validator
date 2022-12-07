<?php

namespace Kostic\Validator;

use Illuminate\Support\Facades\Validator;

class FormValidate
{
    protected $attributes = [];

    protected $scenario = null;

    protected $scenarios = [];

    protected $only = [];

    protected $error = null;

    protected function rules()
    {
        return [];
    }

    protected function messages()
    {
        return [];
    }

    public function scenario(string $scene)
    {
        $this->scenario = $scene;
        return $this;
    }

    protected function take()
    {
        $scene = $this->scenario;

        if (empty($scene)) {
            return true;
        }

        if (!isset($this->scenarios[$scene])) {
            $this->error = trans('messages.validate.scenario_not_exist', ['attribute' => $scene]);
            return false;
        }
        $this->only = [];
        $scene = $this->scenarios[$scene];

        if (is_string($scene)) {
            $scene = explode(',', $scene);
        }
        $this->only = $scene;
        return true;
    }


    public function check(array $data, array $rules = [], array $messages = [])
    {
        $this->error = null;
        $this->attributes = [];
        if (empty($rules)) {
            $rules = $this->rules();
        }
        if (empty($messages)) {
            $messages = $this->messages();
        }
        if (!$this->take()) {
            return false;
        }
        if (!empty($this->only)) {
            $new_rules = [];
            foreach ($this->only as $value) {
                if (array_key_exists($value, $rules)) {
                    $new_rules[$value] = $rules[$value];
                } else {
                    $this->error = trans('messages.validate.rule_not_set', ['attribute' => $value]);
                    return false;
                }
            }
            $rules = $new_rules;
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $this->error = $validator->errors()->first();
            return false;
        }
        $this->attributes = $validator->validated();
        return true;
    }

    public function transit()
    {
        return $this->attributes;
    }

    public function error()
    {
        return $this->error;
    }
}
