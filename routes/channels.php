<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.staff', function ($user) {
    return $user->role !== 'kepala_staff' && $user->email !== 'kepstaff123@gmail.com';
});

Broadcast::channel('notifications.kepala-staff', function ($user) {
    return $user->role === 'kepala_staff' || $user->email === 'kepstaff123@gmail.com';
});
