<?php


namespace App\BusinessLogic\ImportExcel\Impl;


use App\BusinessLogic\ImportExcel\Contracts\IImportExcel;
use App\Dao\Importer\ImporterDao;
use App\Dao\Schools\DepartmentDao;
use App\Dao\Schools\GradeDao;
use App\Dao\Schools\InstituteDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Schools\SchoolDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Models\Acl\Role;
use App\Models\Students\StudentProfile;
use App\User;
use Illuminate\Support\Facades\Hash;
use League\Flysystem\Config;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Ramsey\Uuid\Uuid;

abstract class AbstractImporter implements IImportExcel
{
    protected $config;
    protected $data;
    protected $header=[];
    protected $school;
    protected $importDao;
    protected $skipColoumn = ['startRow', 'dataRow'];
    public function __construct($configArr)
    {
        $this->config = $configArr;
        $this->data   = [];
        $this->importDao = new ImporterDao();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function loadExcelFile()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $filePath = config('filesystems.disks.import')['root'].DIRECTORY_SEPARATOR .$this->config['file_path'];

        $objReader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $objReader->load($filePath);  //$filename可以是上传的表格，或者是指定的表格
        $worksheet = $objPHPExcel->getAllSheets();
        $this->data = $worksheet;
    }

    /**
     * @param $user
     * @return bool
     */
    public function getSchoolId($user)
    {
        $schoolName = $this->config['school']['schoolName'];

        $dao = new SchoolDao($user);
        $schoolObj = $dao->getSchoolByName($schoolName);
        if (!$schoolObj) {
            $schoolObj = $dao->createSchool(['name'=>$schoolName]);
            if ($schoolObj) {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => $schoolName,
                    'table_name'=> 'schools',
                    'result' => json_encode($schoolObj),
                    'task_id' => $this->config['task_id'],
                    'task_status' => 1,
                ]);
            }
        }
        return $schoolObj;
    }

    /**
     * @param $school
     */
    protected function setSchool($school)
    {
        $this->school = $school;
    }

    /**
     * @param $schoolId
     * @return \App\Models\School
     */
    protected function getSchool($schoolId)
    {
        if ($this->school) {
            return $this->school;
        } else {
            $schoolDao = new SchoolDao();
            $schoolObj = $schoolDao->getSchoolById($schoolId);
            $this->setSchool($schoolObj);
            return $schoolObj;
        }
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getSheetIndexArray()
    {
        return array_keys($this->data);
    }

    /**
     * @param $sheetIndex
     * @return mixed
     */
    public function getSheetData($sheetIndex)
    {
        return $this->data[$sheetIndex]->toArray();
    }

    /**
     * @param $row
     * @param $coloumn
     * @param $sheetId
     * @return string
     */
    public function getColoumn($row, $coloumn, $sheetId)
    {
        $header = $this->getHeader($sheetId);
        $defaultValueArr = [];
        $data = $this->getColoumnIndex($coloumn, $header, $sheetId);
        if (empty($data) || empty($data[0])) {
            return $this->getDefaultValue($coloumn, $header, $sheetId);
        } else {
            foreach ($data as $index) {
                $defaultValueArr[] = $row[$index];
            }
            $config = $this->config['school']['sheet'][$sheetId][$coloumn];
            return implode($defaultValueArr, $config['joinSymbol']);
        }
    }

    /**
     * @param $coloumn
     * @param $header
     * @param $sheetId
     * @return array
     */
    public function getColoumnIndex($coloumn, $header, $sheetId)
    {
        $config = $this->config['school']['sheet'][$sheetId][$coloumn];
        $data = [];
        if (!empty($config['coloumnName']))
        {
            foreach ($config['coloumnName'] as $item){
                $data[] = $this->getIndex($item, $header);
            }
            return $data;
        }
    }

    /**
     * @param $name
     * @param $header
     * @param $sheetId
     * @return string
     */
    public function getDefaultValue($name, $header, $sheetId)
    {
        $config = $this->config['school']['sheet'][$sheetId][$name];

        if ($config['isEmpty']=='false')
        {
            return $config['defaultValue'];
        } else {
            return '';
        }
    }

    /**
     * @param $name
     * @param $array
     * @return int|string
     */
    public function getIndex($name, $array)
    {
        if (empty($name))
            return 0;
        foreach ($array as $key=>$value){
            if ($name == $value)
                return $key;
        }
    }

    /**
     * @param $sheetId
     * @return mixed
     */
    public function getHeader($sheetId)
    {
        $header = isset($this->header[$sheetId])?$this->header[$sheetId]:'';
        if (empty($header)) {
            $data = $this->getSheetData($sheetId);
            $sheetConfig = $this->config['school']['sheet'][$sheetId];
            $header = $data[$sheetConfig['startRow']];
            if (empty($header)) {
                exit('文件头获取失败，请检查配置');
            }
            $this->setHeader($header,$sheetId);
            return $header;
        } else {
            return $header;
        }
    }

    /**
     * @param $header
     */
    public function setHeader($header,$sheetId)
    {
        $this->header[$sheetId] = $header;
    }

    /**
     * @param $sheetId
     * @param $row
     * @return array
     */
    public function getRowData($sheetId, $row)
    {
        $data = [];
        $sheetConfig = $this->config['school']['sheet'][$sheetId];
        foreach ($sheetConfig as $key=>$value)
        {
            if (in_array($key, $this->skipColoumn))
                continue;
            $data[$key] = $this->getColoumn($row, $key, $sheetId);
        }
        return $data;
    }

    /**
     * @param $user
     * @param $name
     * @param $schoolId
     * @return bool|mixed
     */
    public function getInstitute($user, $name, $schoolId)
    {
        $instituteDao = new InstituteDao($user);
        $instituteObj =  $instituteDao->getByName($name, $schoolId);
        if ($instituteObj) {
            return $instituteObj;
        } else {
            $schoolObj = $this->getSchool($schoolId);
            $campus = $schoolObj->campuses()->first();

            $institute = $instituteDao->createInstitute([
                'school_id' => $schoolId,
                'campus_id' => $campus->id,
                'name'      => $name,
                'description'      => $name,
            ]);
            if ($institute) {
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => $name,
                        'table_name'=> 'institutes',
                        'result' => json_encode($institute),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);
                return $institute;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => $name,
                    'table_name'=> 'institutes',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $user
     * @param $name
     * @param $schoolId
     * @param $institute
     * @return \App\Models\Schools\Department|bool
     */
    public function getDepartment($user, $name, $schoolId, $institute)
    {
        $departmentDao = new DepartmentDao($user);
        $schoolObj = $this->getSchool($schoolId);
        $campus = $schoolObj->campuses()->first();
        $departmentObj =  $departmentDao->getByName($name, $schoolId,$campus->id,$institute->id);
        if ($departmentObj) {
            return $departmentObj;
        } else {
            $department = $departmentDao->createDepartment([
                'school_id' => $schoolId,
                'campus_id' => $campus->id,
                'institute_id' => $institute->id,
                'name'      => $name,
                'description' => $name,
            ]);
            if ($department) {
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => $name,
                        'table_name'=> 'departments',
                        'result' => json_encode($department),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);
                return $department;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => $name,
                    'table_name'=> 'departments',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $user
     * @param $name
     * @param $schoolId
     * @param $institute
     * @param $department
     * @return \App\Models\Schools\Major|bool|mixed
     */
    public function getMajor($user, $name, $schoolId, $institute, $department)
    {
        $majorDao = new MajorDao($user);
        $magorObj =  $majorDao->getByName($name, $schoolId, $institute->id, $department->id);
        if ($magorObj) {
            return $magorObj;
        } else {
            $schoolObj = $this->getSchool($schoolId);
            $campus = $schoolObj->campuses()->first();

            $major = $majorDao->createMajor([
                'school_id' => $schoolId,
                'campus_id' => $campus->id,
                'institute_id' => $institute->id,
                'department_id' => $department->id,
                'name'      => $name,
                'description' => $name,
            ]);
            if ($major) {
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => $name,
                        'table_name'=> 'majors',
                        'result' => json_encode($major),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);

                return $major;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => $name,
                    'table_name'=> 'majors',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $user
     * @param $name
     * @param $schoolId
     * @param $major
     * @param $year
     * @return \App\Models\Schools\Grade|bool|mixed
     */
    public function getGrade($user, $name, $schoolId, $major, $year)
    {
        $gradeDao = new GradeDao($user);
        $gradeObj =  $gradeDao->getByName($name, $schoolId, $major->id, $year);
        if ($gradeObj) {
            return $gradeObj;
        } else {
            $schoolObj = $this->getSchool($schoolId);
            $grade = $gradeDao->createGrade([
                'school_id' => $schoolId,
                'major_id' => $major->id,
                'year' => $year,
                'name'      => $name,
                'description' => $name,
            ]);
            if ($grade) {
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => $name,
                        'table_name'=> 'grades',
                        'result' => json_encode($grade),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);

                return $grade;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => $name,
                    'table_name'=> 'grades',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $user
     * @param $rowData
     * @param $schoolId
     * @param $institute
     * @param $department
     * @param $major
     * @param $grade
     * @param $row
     * @return \App\Models\Users\GradeUser|bool
     */
    public function getGradeUser($user, $rowData,$schoolId, $institute, $department, $major, $grade, $row)
    {
        $gradeUserDao = new GradeUserDao();
        $gradeUserObj =  $gradeUserDao->getUserInfoByUserId($user->id);
        if ($gradeUserObj) {
            $gradeUserObj->user_type = $user->type;
            $gradeUserObj->save();
            return $gradeUserObj;
        } else {
            $schoolObj = $this->getSchool($schoolId);
            $campus = $schoolObj->campuses()->first();
            $gradeUser = $gradeUserDao->addGradUser([
                'user_id' => $user->id,
                'user_type' => $user->type,
                'name' => $rowData['userName'],
                'school_id' => $schoolId,
                'campus_id' => $campus->id,
                'institute_id' => $institute->id,
                'department_id' => $department->id,
                'major_id' => $major->id,
                'grade_id' => $grade->id,
                'last_updated_by' => 0,
            ]);
            if ($gradeUser) {
                $gradeUser = $gradeUserDao->getUserInfoByUserId($user->id);
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => json_encode($row),
                        'table_name'=> 'grade_users',
                        'result' => json_encode($gradeUser),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);

                return $gradeUser;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => json_encode($row),
                    'table_name'=> 'grade_users',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $mobile
     * @param $name
     * @param $passwdTxt
     * @param $row
     * @return \App\User|bool|mixed
     * @throws \Exception
     */
    public function getUser($mobile, $name, $passwdTxt,$row)
    {
        $userDao = new UserDao();
        $importUser =  $userDao->getUserByMobile($mobile);
        if ($importUser) {
            $this->modifytUser($importUser, $passwdTxt);
            return $importUser;
        } else {
            $importUser = $userDao->importUser($mobile,$name,$passwdTxt);
            if ($importUser) {
                $this->importDao->writeLog([
                        'type' =>1,
                        'source' => json_encode($row),
                        'table_name'=> 'users',
                        'result' => json_encode($importUser),
                        'task_id' => $this->config['task_id'],
                        'task_status' => 1,
                    ]);

                return $importUser;
            } else {
                $this->importDao->writeLog([
                    'type' =>1,
                    'source' => json_encode($row),
                    'table_name'=> 'users',
                    'task_id' => $this->config['task_id'],
                    'task_status' => 2,
                ]);
                return false;
            }
        }
    }

    /**
     * @param $row
     * @param string $result
     * @param string $tableName
     * @param string $target
     * @param int $type
     * @param int $status
     */
    public function writeLog($row, $result='', $tableName='', $target='', $type=3, $status=2)
    {
       $result = $this->importDao->writeLog([
            'type' => $type,
            'source' => json_encode($row),
            'target' => $target?json_encode($target):'',
            'table_name'=> $tableName,
            'result'=> $result?json_encode($result):'',
            'task_id' => $this->config['task_id'],
            'task_status' => $status,
        ]);
    }


    public function saveStudent($user, $rowData,$row)
    {
        //$student = new StudentProfile();
        $studentDao = new StudentProfileDao();
        $student = $studentDao->getStudentInfoByUserId($user->id);
        if (empty($student)) {
            $student = new StudentProfile();
        }
        $student->user_id = $user->id;
        $student->uuid = Uuid::uuid4()->toString();;
        $student->year = $rowData['year'];
        $student->serial_number = "-";
        if ($rowData['gender'] == '男'){
            $gender = 1;
        }else {
            $gender = 2;
        }
        $student->gender = $gender;
        $student->country = $rowData['country'];
        $student->state = $rowData['state'];
        $student->city = $rowData['city'];
        $student->postcode = $rowData['postCode'];
        $student->area = $rowData['area'];
        $student->address_line = $rowData['addressLine'];
        $student->id_number = $rowData['idNumber'];
        $student->birthday = strtotime(substr($rowData['idNumber'],6,8));
        $student->political_code = isset($rowData['politicalName'])?$rowData['politicalName']:'';
        $student->nation_name = $rowData['nation'];
        $student->parent_name = "-";
        $student->parent_mobile = "-";
        $student->avatar = "";
        $result = $student->save();
        if ($result) {
            $studentDao = new StudentProfileDao();
            $student = $studentDao->getStudentInfoByUserId($user->id);
            $this->importDao->writeLog([
                'type' => 1,
                'source' => json_encode($row),
                'table_name' => 'users',
                'result' => json_encode($student),
                'task_id' => $this->config['task_id'],
                'task_status' => 1,
            ]);

            return $student;
        } else {
            $this->importDao->writeLog([
                'type' => 1,
                'source' => json_encode($row),
                'table_name' => 'users',
                'task_id' => $this->config['task_id'],
                'task_status' => 2,
            ]);
            return false;
        }

    }
    public function modifytUser($user, $passwordInPlainText)
    {
        $user->password = Hash::make($passwordInPlainText);
        $user->status = User::STATUS_VERIFIED;
        $user->type = Role::VERIFIED_USER_STUDENT;
        return $user->save();
    }
}
