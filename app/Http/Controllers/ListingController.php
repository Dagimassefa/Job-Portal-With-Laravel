<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ListingController extends Controller
{
    // show all listing
    public function index()
    {
        // dd(request('tag'));
        return view('listings.index', [

            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);
    }
    //    show single listing
    public function show(Listing $listing)
    {
        return View('listings.show', [
            'listing' => $listing
        ]);
    }
    public function create()
    {
        return view('listings.create');
    }
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $formFields['user_id'] = auth()->id();
        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing Created Successfully!');
    }

    //show edit form
    public function edit(Listing $listing)
    {
        return view('listings.edit', ['listing' => $listing]);
    }

    public function update(Request $request, Listing $listing)
    {

        //make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'unautorized action!');
        }
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $listing->update($formFields);

        return back()->with('message', 'Listing Updated Successfully!');
    }

    public function destroy(Listing $listing)
    {
        //make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'unautorized action!');
        }
        $listing->delete();
        return redirect('/')->with('message', 'Listing Deleted Successfully');
    }

    //manage listing
    public function manage()
    {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
