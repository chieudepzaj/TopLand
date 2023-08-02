<?php

namespace Botble\RealEstate\Commands;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\RealEstate\Enums\ModerationStatusEnum;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Repositories\Interfaces\PropertyInterface;
use Illuminate\Console\Command;
use RealEstateHelper;

class RenewPropertiesCommand extends Command
{
    public PropertyInterface $propertyRepository;

    protected $signature = 'cms:properties:renew';

    protected $description = 'Renew properties';

    public function __construct(PropertyInterface $propertyRepository)
    {
        parent::__construct();
        $this->propertyRepository = $propertyRepository;
    }

    public function handle()
    {
        $properties = $this->propertyRepository->getModel()
            ->expired()
            ->where('re_properties.status', BaseStatusEnum::PUBLISHED)
            ->where('moderation_status', ModerationStatusEnum::APPROVED)
            ->where('auto_renew', 1);

        if (RealEstateHelper::isEnabledCreditsSystem()) {
            $properties = $properties
                ->where('author_type', Account::class)
                ->join('re_accounts', 're_accounts.id', '=', 're_properties.author_id')
                ->where('credits', '>', 0);
        }

        $properties = $properties
            ->with(['author'])
            ->select('re_properties.*')
            ->get();

        foreach ($properties as $property) {
            if (RealEstateHelper::isEnabledCreditsSystem() && $property->author->credits <= 0) {
                continue;
            }

            $property->expire_date = now()->addDays(RealEstateHelper::propertyExpiredDays());
            $property->save();

            if (RealEstateHelper::isEnabledCreditsSystem()) {
                $property->author->credits--;
            }

            $property->author->save();
        }

        $this->info('Renew ' . $properties->count() . ' properties successfully!');
    }
}
