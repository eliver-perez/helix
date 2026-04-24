<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\StaffRepository;
use App\Repositories\GenderRepository;
use App\Repositories\LocationRepository;
use App\Repositories\UserRoleRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SpecialtyRepository;
use InvalidArgumentException;
use RuntimeException;

class StaffService extends Service
{
    public function __construct(
        private StaffRepository $staffRepository,
        private GenderRepository $genderRepository,
        private LocationRepository $locationRepository,
        private UserRoleRepository $userRoleRepository,
        private RoleRepository $roleRepository,
        private SpecialtyRepository $specialtyRepository
    ) {
    }

    public function create(array $data): int {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');

        $firstName = $this->normalizeRequiredText(
            $data['first_name'] ?? null,
            'El nombre es obligatorio.'
        );

        $lastName = $this->normalizeRequiredText(
            $data['last_name'] ?? null,
            'El apellido es obligatorio.'
        );

        $lastName2 = $this->normalizeOptionalText($data['last_name_2'] ?? null);
        $dob = $this->formatDateToSQL($data['dob'] ?? null);

        $genderId = $this->normalizeRequiredText(
            $data['gender'] ?? null,
            'El género es obligatorio.'
        );

        $curp = $this->normalizeOptionalText($data['curp'] ?? null);

        $street = $this->normalizeOptionalText($data['street'] ?? null);
        $ext_no = $this->normalizeOptionalText($data['ext_no'] ?? null);
        $int_no = $this->normalizeOptionalText($data['int_no'] ?? null);
        $locality = $this->normalizeOptionalInt($data['locality'] ?? null);

        $email = $this->normalizeOptionalText($data['email'] ?? null);
        $phone = $this->normalizeOptionalText($data['phone'] ?? null);
        $mobile = $this->normalizeOptionalText($data['mobile'] ?? null);

        $username = $this->normalizeOptionalText($data['username'] ?? null);
        $userRole = $this->normalizeOptionalInt($data['user_role'] ?? null);
        $password = $this->normalizeOptionalText($data['password'] ?? null);
        
        if($password != null)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $role = $this->normalizeOptionalInt($data['role'] ?? null);

        $cedula = $this->normalizeOptionalText($data['cedula'] ?? null);
        $specialty = $this->normalizeOptionalInt($data['specialty'] ?? null);
        $university = $this->normalizeOptionalText($data['university'] ?? null);
        $university_grad_year = $this->normalizeOptionalInt($data['university_grad_year'] ?? null);
        $university_municipality = $this->normalizeOptionalInt($data['university_municipality'] ?? null);

        $rfc = $this->normalizeOptionalText($data['rfc'] ?? null);
        $salary = $this->normalizeOptionalFloat($data['salary'] ?? null);

        if (!$this->genderRepository->existsById($genderId)) {
            throw new InvalidArgumentException('El género indicado no existe.');
        }

        if($role != null && !$this->roleRepository->existsById($role)) {
            throw new InvalidArgumentException('El puesto no existe.');
        }

        if($userRole != null && !$this->userRoleRepository->existsById($userRole)) {
            throw new InvalidArgumentException('El tipo de usuario no existe.');
        }

        if($specialty != null && !$this->specialtyRepository->existsById($specialty)) {
            throw new InvalidArgumentException('La especialidad no existe.');
        }

        if($locality != null && !$this->locationRepository->localityExists($locality)) {
            throw new InvalidArgumentException('La colonia seleccionada no existe.');
        }

        if($university_municipality != null && !$this->locationRepository->municipalityExists($university_municipality)) {
            throw new InvalidArgumentException('El municipio de la universidad seleccionado no existe.');
        }

        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('El correo electrónico no es válido.');
            }

            if ($this->staffRepository->emailExists($email)) {
                throw new RuntimeException('Ya existe un registro de personal con ese correo.');
            }
        }

        $hasMedicalData =
            $cedula !== null ||
            $university !== null ||
            $university_grad_year !== null;

        $isMedical = $hasMedicalData;

        if ($isMedical && $specialty === null) {
            throw new InvalidArgumentException('La especialidad es obligatoria para registrar el perfil profesional.');
        }

        if ($isMedical && $university_municipality === null) {
            throw new InvalidArgumentException('El municipio de la universidad es obligatorio para registrar el perfil profesional.');
        }

        $hasUserData =
            $username !== null ||
            $password !== null ||
            $userRole !== null;

        $createUser = $hasUserData;

        if ($hasUserData) {
            if ($username === null) {
                throw new InvalidArgumentException('El nombre de usuario es obligatorio para crear la cuenta de acceso.');
            }

            if ($password === null) {
                throw new InvalidArgumentException('La contraseña es obligatoria para crear la cuenta de acceso.');
            }

            if ($userRole === null) {
                throw new InvalidArgumentException('El tipo de usuario es obligatorio para crear la cuenta de acceso.');
            }

            if($this->staffRepository->userExists($username)) {
                throw new RuntimeException('Ya existe un registro con ese nombre de usuario.');
            }
        }

        $fullName = $firstName . ' ' . $lastName . ($lastName2 !== null ? ' ' . $lastName2 : '');

        $conn = $this->staffRepository->getConnection();
        $conn->beginTransaction();
        try {
            $staffUuid = $this->generateUuidBinary();
            $staffId = $this->staffRepository->insertStaff([
                    'uuid'                          => $staffUuid,
                    'first_name'                    => $firstName,
                    'last_name'                     => $lastName,
                    'last_name_2'                   => $lastName2,
                    'dob'                           => $dob,
                    'gender'                        => $genderId,
                    'curp'                          => $curp,
                    'street'                        => $street,
                    'ext_no'                        => $ext_no,
                    'int_no'                        => $int_no,
                    'locality'                      => $locality,
                    'email'                         => $email,
                    'phone'                         => $phone,
                    'mobile'                        => $mobile,
                    'role'                          => $role,
                    'rfc'                           => $rfc,
                ]);
            
            $this->staffRepository->insertStaffRegistration($staffId, [
                'f_alta'                            => date('Y-m-d')
                ]);

            if($isMedical) {
                $this->staffRepository->insertStaffProfessional($staffId, [
                        'cedula'                            => $cedula,
                        'specialty'                         => $specialty,
                        'university'                        => $university,
                        'university_grad_year'              => $university_grad_year,
                        'university_municipality'           => $university_municipality
                    ]);
            }

            if($createUser) {
                $userUuid = $this->generateUuidBinary();
                $userId = $this->staffRepository->insertUser([
                            'uuid'                              => $userUuid,
                            'name'                              => $fullName,
                            'username'                          => $username,
                            'password_hash'                     => $password_hash,
                            'user_role'                         => $userRole,
                        ]);
                $this->staffRepository->insertStaffUser($staffId, $userId);
            }

            if($salary) {
                $this->staffRepository->insertStaffSalary($staffId, [
                    'salary'                                    => $salary,
                    'uid'                                       => $uid,
                    'salary_since'                              => date('Y-m-d')
                ]);
            }
            $conn->commit();
            return $staffId;
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}