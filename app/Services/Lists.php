<?php
namespace App\Services;

/** Reference lists shared by validation and views. */
class Lists
{
    public const COUNTRIES = ['Uganda', 'Kenya', 'Tanzania', 'Rwanda', 'Burundi', 'South Sudan', 'Ethiopia', 'Somalia', 'DR Congo', 'Other'];
    public const AGE_GROUPS = ['18-24', '25-30', '31-35'];
    public const DISCIPLINES = ['Agriculture', 'Software development', 'Data analysis', 'Environmental studies', 'Business & entrepreneurship',
        'Health', 'Education', 'Design & communication', 'Engineering', 'Social sciences', 'Other'];
    public const THEMES = ['Agriculture & food security', 'Climate & environment', 'Health', 'Education', 'Jobs & entrepreneurship',
        'Digital inclusion', 'Gender & inclusion', 'Water & sanitation', 'Other'];

    public static function csv(array $list): string { return implode(',', $list); }

    public static function homeFor(string $role): string
    {
        switch ($role) {
            case 'admin': return 'admin';
            case 'facilitator': return 'manage/courses';
            case 'mentor':
            case 'stakeholder': return 'staff/projects';
            default: return 'my-learning';
        }
    }
}
