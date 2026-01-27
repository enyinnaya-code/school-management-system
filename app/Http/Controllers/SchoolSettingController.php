<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $settings = SchoolSetting::first() ?? new SchoolSetting();
        return view('settings.school', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:150', // max 150KB
        ]);

        // Get the first record or create with default data
        $settings = SchoolSetting::first();
        
        if (!$settings) {
            // If no record exists, create one with the submitted data
            $settings = new SchoolSetting();
        }

        $data = $request->only(['school_name', 'address']);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo && Storage::disk('public')->exists('logos/' . $settings->logo)) {
                Storage::disk('public')->delete('logos/' . $settings->logo);
            }

            $file = $request->file('logo');
            $filename = 'school_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('logos', $filename, 'public');

            $data['logo'] = $filename;
        }

        // Update or fill and save
        $settings->fill($data);
        $settings->save();

        return redirect()->back()->with('success', 'School settings updated successfully!');
    }
}