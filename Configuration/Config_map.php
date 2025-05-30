<?php
// Constantes para campos de información de activos
const ASSETNAME = 'assetname';
const SERIAL_NUMBER = 'serial_number';

// Constantes para equipos adicionales
const HEADSET = 'HeadSet';
const DONGLE = 'Dongle';
const CARNET = 'Carnet';
const LLAVE = 'LLave';

// Constantes para información de usuario
const USER_STATUS = 'user_status';
const LAST_USER = 'last_user';
const JOB_TITLE = 'job_title';
const STATUS_CHANGE = 'status_change';

// Constantes para identificación
const CEDULA = 'cedula';
const TIPO_ID = 'Tipo_ID';
const FECHA_SALIDA = 'fecha_salida';
const FECHA_INGRESO = 'fecha_ingreso';

// Mapeo de campos por categorías
$fieldMap = [
    // Información básica del activo
    ASSETNAME => [
        ASSETNAME,
        'asset',
        'Assetname',
        'NameAsset',
        'Asset_Title',
        'Asset_ID',
        'Asset_Identifier',
        'AssetName',
        'Activos'
    ],
    SERIAL_NUMBER => [
        'serialnumber',
        SERIAL_NUMBER,
        'serial',
        'Serial_ID',
        'Serial_No',
        'SerialKey',
        'Serial',
        'SerialNumber',
        'Serial Number',
        'SERIAL PC'
    ],


    // Equipos adicionales
    HEADSET => [
        HEADSET,
        'headset',
        'Head_Set',
        'head_set',
        'Headphone',
        'Audio_Device',
        'Head Set',
        'SERIAL HEADSET'
    ],
    DONGLE => [
        DONGLE,
        'dongle',
        'Dongle_ID'
    ],
    CARNET => [
        CARNET,
        'carnet',
        'Carnet_ID',
        'Carnet_Number'
    ],
    LLAVE => [
        LLAVE,
        'llave',
        'key',
        'Key',
        'Locker_Key',
        'Access_Key'
    ],

    // Información de usuario
    USER_STATUS => [
        USER_STATUS,
        'userstatus',
        'status_user',
        'user_staus',
        'User_Condition',
        'Account_Status',
        'UserStatus',
        'User Status'
    ],
    LAST_USER => [
        LAST_USER,
        'user',
        'Last_user',
        'Previous_User',
        'Last_Updated_By',
        'Last User'
    ],
    JOB_TITLE => [
        JOB_TITLE,
        'job title',
        'title',
        'Job_Position',
        'Position_Title',
        'jobtitle',
        'Job Title'
    ],
    STATUS_CHANGE => [
        'statuschange',
        STATUS_CHANGE,
        'Status_change',
        'Status_Change',
        'Change_Status',
        'Update_Status'
    ],

    // Información de identificación
    CEDULA => [
        CEDULA,
        'id',
        'identification',
        'ID',
        'Identification_Number',
        'ID_Number',
        'Identificacion'
    ],
    TIPO_ID => [
        TIPO_ID,
        'idtype',
        'TypeID',
        'Identification_Type',
        'ID_Type',
        'Tipo de Identificación',
        'tipo de identificacion'
    ],
    FECHA_SALIDA => [
        'fechasalida',
        'date',
        FECHA_SALIDA,
        'Fecha_salida',
        'Departure_Date',
        'Exit_Date',
        'fecha salida',
        'Fecha Salida'
    ],
    FECHA_INGRESO => [
        FECHA_INGRESO,
        'fechaingreso',
        'fecha_ingreso',
        'date_entry',
        'Entry_Date',
        'Admission_Date',
        'Start_Date',
        'Fecha Ingreso',
        'fecha ingreso'
    ]
];