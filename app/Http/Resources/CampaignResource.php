<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'createdAt' => $this->created_date,
            'sequenceType' => $this->renameSequenceType(),
            'notProcessConditionIf' => $this->process_condition ? json_decode($this->process_condition) : [],
            // 'campaignList' => CampaignListResource::collection($this->campaignList)
        ];
    }

    private function renameSequenceType()
    {
        switch ($this->sequence_type) {
            case 'lead_gen':
                $this->sequence_type = 'Lead generation';
                break;
            case 'endorse':
                $this->sequence_type = 'Endorse';
                break;
            case 'profile_views':
                $this->sequence_type = 'Profile views';
                break;
            case 'custom':
                $this->sequence_type = 'Custom';
                break;
        }
        return $this->sequence_type;
    }
}
