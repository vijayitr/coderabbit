<?php

use App\Models\Notification;
use App\Events\MessageSent;

if (!function_exists('pred')) {
    function pred($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}

if (!function_exists('pre')) {
    function pre($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('formatPermissionName')) {
    function formatPermissionName(string $permission): string {
        $formatted = ucwords(str_replace('_', ' ', $permission)); 
        return $formatted;
    }
}

if (!function_exists('getFirstCapital')) {
    function getFirstCapital($name) {
        if (!empty($name)) {
            return strtoupper(substr($name, 0, 1));
        }
        return '';
    }
}

if (!function_exists('getRoleNames')) {
    function getRoleNames($user) {
        if (!empty($user)) {
            return implode(', ', array_map('ucwords', $user->getRoleNames()->toArray()));
        }
        return '';
    }
}

if (!function_exists('getPriorityNames')) {
    function getPriorityNames($id = null) {
        $priorityMap = [
            4 => 'Low Priority',
            3 => 'Normal',
            2 => 'High Priority',
            1 => 'Urgent'
        ];

        return $id ? ($priorityMap[$id] ?? '') : $priorityMap;
    }
}

if (!function_exists('getPriorityBadge')) {
    function getPriorityBadge($priority = null) {
        $badges = [
            1 => ['label' => 'Urgent', 'class' => 'badge bg-danger'],
            2 => ['label' => 'High Priority', 'class' => 'badge bg-warning text-dark'],
            3 => ['label' => 'Normal', 'class' => 'badge bg-primary'],
            4 => ['label' => 'Low Priority', 'class' => 'badge bg-secondary'],
        ];

        if (isset($badges[$priority])) {
            return '<span class="' . $badges[$priority]['class'] . ' p-3">' . $badges[$priority]['label'] . '</span>';
        }

        return $badges;
    }
}

if (!function_exists('timeToSeconds')) {
    function timeToSeconds($time) {
        sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
        return $hours * 3600 + $minutes * 60 + $seconds;
    }
}

if (!function_exists('secondsToTime')) {
    function secondsToTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}

if (!function_exists('sendNotification')) {
    function sendNotification(array $data): void
    {
        if (isset($data['type'])) {
            $temp_data = $data;
            $data = [];
            $data[] = $temp_data;
        }
        foreach ($data as $key => $value) {
            $insertData = array_merge([
                'created_at' => now(),
                'updated_at' => now(),
                'assigned_by' => Auth()->user()->id
            ], $value);
            Notification::insert($insertData);
            event(new MessageSent($insertData, $insertData['user_id']));
        }
    }
}