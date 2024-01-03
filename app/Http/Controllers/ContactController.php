<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;
use App\Repositories\CompanyRepository;

class ContactController extends Controller
{
    protected function userCompanies()
    {
        return Company::forUser(auth()->user())->orderBy('name')->pluck('name', 'id');
    }

    public function index()
    {


        // $data =[];

        $companies = Company::orderBy('name')->pluck('name', 'id');
        //DB::enableQueryLog();
        $contacts = Contact::latest()->where(function ($query) {
            if ($companyId = request()->query('company_id')) {

                $query->where('company_id', $companyId);
            }

            if ($search = request()->query('search')) {
                $query->where("first_name", "LIKE", "%{$search}%");
                $query->orWhere("last_name", "LIKE", "%{$search}%");
                $query->orWhere("email", "LIKE", "%{$search}%");
            }
        })->paginate(10);

        //dump(DB::getQueryLog());



        // foreach ($companies as $company) {

        //     $data[$company->id] = $company->name . " ( " . $company->contacts()->count() . " ) ";
        // }

        return view('contacts.index', [
            "contacts" => $contacts,
            'companies' => $companies
        ]);
    }

    public function create()
    {
        $companies = $this->userCompanies();
        $contact = new Contact();

        return view('contacts.create', compact('companies', 'contact'));
    }

    public function store(ContactRequest $request)
    {
        $request->user()->contacts()->create($request->all());
        return redirect()->route('contacts.index')->with('message', 'Contact has been added successfully');
    }

    function edit(Contact $contact)
    {
        $companies = Company::orderBy('name')->pluck('name', 'id');


        return view('contacts.edit', [
            'companies' => $companies,
            'contact' => $contact


        ]);
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        $contact->update($request->all());
        return redirect()->route('contacts.index')->with('message', 'Contact has been updated successfully');
    }

    function destroy($id)
    {

        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('message', 'Contact has been moved to trash');
    }

    public function restore(Contact $contact)
    {
        $contact->restore();
        return back()
            ->with('message', 'Contact has been restored from trash.')
            ->with('undoRoute', getUndoRoute('contacts.destroy', $contact));
    }

    public function forceDelete(Contact $contact)
    {
        $contact->forceDelete();
        return back()
            ->with('message', 'Contact has been removed permanently.');
    }

    public function show(Contact $contact)
    {
        return view('contacts.show')->with('contact', $contact);
    }
}
