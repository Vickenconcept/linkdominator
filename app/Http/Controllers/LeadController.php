<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audience;
use App\Models\AudienceList;
use App\Models\SnLead;
use App\Models\SnLeadsCompany;
use App\Models\SnLeadList;
use App\Helpers\CustomQueryHelper;
use Log;

class LeadController extends Controller
{
    use CustomQueryHelper;

    public function index()
    {
        $leadlist = $this->getLeadList(auth()->user()->id);

        return view('leads.index', compact('leadlist'));
    }

    public function search_leadlist(Request $request)
    {
        $search = $request->query('search');

        if(isset($search)){
            $leadlist = $this->searchLeadList(auth()->user()->id, $search);

            return view('leads.index', compact('leadlist'));
        }

        return redirect()->route('leads.list');
    }

    public function search_leads(Request $request)
    {
        $search = $request->query('search');
        $src = $request->query('src');
        $list_id = $request->query('list_id');

        if($search){
            $leads = $this->searchLeads($list_id, $src, $search);

            if($src == 'aud'){
                $leadlist = Audience::select('audience_name as name')->where('audience_id', $listId)->first();
            }else{
                $leadlist = SnLeadList::select('name')->where('list_hash', $listId)->first();
            }

            return view('leads.leads', compact('leads', 'leadlist'));
        }

        return redirect()->route('leads.show', ['listId' => $list_id, 'src' => $src]);        
    }

    public function show(Request $request, $listId)
    {
        $src = $request->query('src');
        $leads = [];
        $leadlist = [];

        if(isset($src)){
            $leads = $this->allLeads($listId, $src);

            if($src == 'aud'){
                $leadlist = Audience::select('audience_name as name')->where('audience_id', $listId)->first();
            }else{
                $leadlist = SnLeadList::select('name')->where('list_hash', $listId)->first();
            }
        }

        return view('leads.leads', compact('leads', 'leadlist'));
    }

    public function update(Request $request, $listHash)
    {
        $data = $request->all();

        if($data['list_source'] == 'Audience'){
            Audience::where('id', $data['id'])
                ->update([
                    'audience_name' => $data['list_name']
                ]);
        }else {
            SnLeadList::where('id', $data['id'])
                ->update([
                    'name' => $data['list_name']
                ]);
        }

        notify()->success('List updated successfully');
        return redirect()->route('leads.list');
    }

    public function remove_leadlist(Request $request, $listId)
    {
        $src = $request->src;
        
        if($src == 'aud'){
            AudienceList::where('audience_id', $listId)->delete();
            Audience::where('audience_id', $listId)->delete();
        }else {
            SnLead::where('sn_list_id', $listId)->delete();
            SnLeadsCompany::where('sn_lead_id', $listId)->delete();
            SnLeadList::where('list_hash', $listId)->delete();
        }

        notify()->success('Lead list removed successfully');
        return redirect()->route('leads.list');
    }
    
    public function remove_lead(Request $request, $leadId)
    {
        $src = $request->src;
        $list_id = $request->list_id;

        if($src == 'aud'){
            AudienceList::where('id', $leadId)->delete();
        }else {
            SnLead::where('id', $leadId)->delete();
        }

        notify()->success('Lead removed successfully');
        return redirect()->route('leads.show', ['listId' => $list_id, 'src' => $src]);
    }

    public function remove_lead_bulk(Request $request, $listId)
    {
        $src = $request->query('src');
        $ids = $request->query('ids');

        if($src == 'aud'){
            AudienceList::whereIn('id', explode(',', $ids))->delete();
        }else {
            SnLead::whereIn('id', explode(',', $ids))->delete();
        }

        notify()->success('Lead removed successfully');
        return redirect()->route('leads.show', ['listId' => $listId, 'src' => $src]);
    }

    public function export(Request $request)
    {
        $list_id = $request->query('hash');
        $src = $request->query('src');
        $exp_format = $request->query('format');

        if($src == 'sn'){
            $leads = $this->snLeadExport($list_id);
        }else {
            $leads = $this->audienceExport($list_id);
        }
        
        return response()->json([
            'data' => $leads
        ]);
    }

    public function bulk_export(Request $request)
    {
        $src = $request->query('src');
        $ids = $request->query('ids');

        if($src == 'sn'){
            $leads = $this->snLeadExport($ids, 'bulk');
        }else {
            $leads = $this->audienceExport($ids, 'bulk');
        }
        
        return response()->json([
            'data' => $leads
        ]);
    }
}
