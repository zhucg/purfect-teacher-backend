<?php


namespace App\Http\Requests\Evaluate;


use App\Http\Requests\MyStandardRequest;

class EvaluateRequest extends MyStandardRequest
{

    public function getFormDate() {
        $data = $this->get('evaluate');
        $data['school_id'] = $this->getSchoolId();
        return $data;
    }


    public function getEvaluateStudentFormDate() {
        $data = $this->get('evaluate');
        $data['school_id'] = $this->getSchoolId();
        return $data;
    }


    public function getAttendanceId() {
        return $this->get('attendance_id');
    }
}
