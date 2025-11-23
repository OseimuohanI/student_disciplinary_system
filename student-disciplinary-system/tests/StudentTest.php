<?php

use PHPUnit\Framework\TestCase;
use App\Model\Student;

class StudentTest extends TestCase
{
    protected $student;

    protected function setUp(): void
    {
        $this->student = new Student();
    }

    public function testStudentInitialization()
    {
        $this->assertInstanceOf(Student::class, $this->student);
    }

    public function testSetAndGetFirstName()
    {
        $this->student->setFirstName('John');
        $this->assertEquals('John', $this->student->getFirstName());
    }

    public function testSetAndGetLastName()
    {
        $this->student->setLastName('Doe');
        $this->assertEquals('Doe', $this->student->getLastName());
    }

    public function testSetAndGetEmail()
    {
        $this->student->setEmail('john.doe@example.com');
        $this->assertEquals('john.doe@example.com', $this->student->getEmail());
    }

    public function testSetAndGetPhone()
    {
        $this->student->setPhone('1234567890');
        $this->assertEquals('1234567890', $this->student->getPhone());
    }

    public function testSetAndGetDOB()
    {
        $dateOfBirth = new DateTime('2000-01-01');
        $this->student->setDOB($dateOfBirth);
        $this->assertEquals($dateOfBirth, $this->student->getDOB());
    }

    public function testSetAndGetEnrollmentNo()
    {
        $this->student->setEnrollmentNo('EN123456');
        $this->assertEquals('EN123456', $this->student->getEnrollmentNo());
    }
}