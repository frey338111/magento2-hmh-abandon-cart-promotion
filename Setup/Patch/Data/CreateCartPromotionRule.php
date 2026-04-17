<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Setup\Patch\Data;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;

class CreateCartPromotionRule implements DataPatchInterface
{
    private const FIXED_RULE_NAME = 'HMH_CART_PROMOTION fix price discount code';
    private const PERCENT_RULE_NAME = 'HMH_CART_PROMOTION percentage discount code';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly RuleFactory $ruleFactory,
        private readonly RuleCollectionFactory $ruleCollectionFactory,
        private readonly CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly State $appState,
        private readonly Json $serializer
    ) {
    }

    public function apply(): void
    {
        $this->setAdminAreaCode();
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->saveRule(
            self::FIXED_RULE_NAME,
            '£5 off your basket',
            Rule::CART_FIXED_ACTION,
            5
        );
        $this->saveRule(
            self::PERCENT_RULE_NAME,
            '10% off your basket',
            Rule::BY_PERCENT_ACTION,
            10
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function saveRule(
        string $ruleName,
        string $description,
        string $simpleAction,
        float|int $discountAmount
    ): void {
        $rule = $this->getRule($ruleName);
        $rule->setName($ruleName);
        $rule->setDescription($description);
        $rule->setIsActive(1);
        $rule->setStopRulesProcessing(0);
        $rule->setIsAdvanced(1);
        $rule->setWebsiteIds($this->getWebsiteIds());
        $rule->setCustomerGroupIds($this->getCustomerGroupIds());
        $rule->setUsesPerCustomer(1);
        $rule->setUsesPerCoupon(1);
        $rule->setSimpleAction($simpleAction);
        $rule->setDiscountAmount($discountAmount);
        $rule->setApplyToShipping(0);
        $rule->setSimpleFreeShipping(0);
        $rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $rule->setUseAutoGeneration(1);
        $rule->setStoreLabels([]);
        $rule->setFromDate(null);
        $rule->setToDate(null);
        $rule->setConditionsSerialized($this->serializer->serialize([]));
        $rule->setActionsSerialized($this->serializer->serialize([]));
        $rule->save();
    }

    private function getRule(string $ruleName): Rule
    {
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('name', $ruleName);
        $collection->setPageSize(1);

        /** @var Rule $rule */
        $rule = $collection->getFirstItem();

        if ($rule->getId()) {
            return $rule;
        }

        return $this->ruleFactory->create();
    }

    /**
     * @return int[]
     */
    private function getWebsiteIds(): array
    {
        $websiteIds = [];

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteIds[] = (int) $website->getId();
        }

        return $websiteIds;
    }

    /**
     * @return int[]
     */
    private function getCustomerGroupIds(): array
    {
        return array_values(
            array_filter(
                $this->customerGroupCollectionFactory->create()->getAllIds(),
                static fn (int $groupId): bool => $groupId !== GroupInterface::NOT_LOGGED_IN_ID
            )
        );
    }

    private function setAdminAreaCode(): void
    {
        try {
            $this->appState->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException) {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        }
    }
}
