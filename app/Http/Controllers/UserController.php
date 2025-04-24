<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class UserController extends Controller
{
  public function index()
  {
    $users = User::with('department', 'position', 'grade')->get();
    return view('users.index', compact('users'));
  }

  public function show(User $user)
  {
    return view('users.show', compact('user'));
  }

  public function edit(User $user)
  {
    return view('users.edit', compact('user'));
  }

  public function update(Request $request, User $user)
  {
    $request->validate([
      'mobile_number' => 'required|string|max:20',
      'personal_email' => 'required|email',
    ]);

    $user->update($request->only(['mobile_number', 'personal_email']));
    return redirect()->route('users.index')->with('success', 'User updated.');
  }
}
