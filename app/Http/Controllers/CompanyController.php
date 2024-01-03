<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyRequest;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     $companies = Company::allowedTrash()
    //         ->allowedSorts(['name', 'website', 'email'], '-id')
    //         ->allowedSearch('name', 'website', 'email')
    //         ->forUser(auth()->user())
    //         ->withCount("contacts")
    //         ->paginate(10);

    //     return view('companies.index', compact('companies'));
    // }

    public function index()
    {


        // $data =[];

        //DB::enableQueryLog();
        $companies = Company::latest()->where(function ($query) {
            if ($companyId = request()->query('company_id')) {

                $query->where('company_id', $companyId);
            }

            if ($search = request()->query('search')) {
                $query->where("name", "LIKE", "%{$search}%");
                $query->orWhere("website", "LIKE", "%{$search}%");
                $query->orWhere("email", "LIKE", "%{$search}%");
            }
        })->paginate(10);

        //dump(DB::getQueryLog());



        // foreach ($companies as $company) {

        //     $data[$company->id] = $company->name . " ( " . $company->contacts()->count() . " ) ";
        // }

        return view('companies.index', compact('companies'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company = new Company();

        return view('companies.create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        $request->user()->companies()->create($request->validated());

        return redirect()->route('companies.index')->with('message', 'Company has been added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return view("companies.show", compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CompanyRequest $request, Company $company)
    {
        $company->update($request->all());

        return redirect()->route('companies.index')->with('message', 'Company has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->delete();
        $redirect = request()->query('redirect');
        return ($redirect ? redirect()->route($redirect) : back())
            ->with('message', 'Company has been moved to trash.');
    }


    public function restore(Company $company)
    {
        $company->restore();
        return back()
            ->with('message', 'Company has been restored from trash.')
            ->with('undoRoute', getUndoRoute('companies.destroy', $company));
    }

    public function forceDelete(Company $company)
    {
        $company->forceDelete();
        return back()
            ->with('message', 'Company has been removed permanently.');
    }
}