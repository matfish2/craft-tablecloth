<?php


namespace tableclothtests\_craft\migrations\services;


class ProductsFeedGenerator extends FeedGenerator
{
    protected bool $hasVariants;

    /**
     * ProductsFeedGenerator constructor.
     * @param bool $hasVariants
     */
    public function __construct(bool $hasVariants)
    {
        $this->hasVariants = $hasVariants;
    }

    /**
     * @throws \Exception
     */
    protected function getNativeFieldsData(): array
    {
        return [
            'title' => FakerService::words(random_int(3, 5)),
            'description' => FakerService::sentence(),
            'postDate'=>FakerService::date(),
            'expiryDate'=>FakerService::date(),
            'variant' => $this->getVariants(),
            'enabled'=>FakerService::boolean(0.2),
            'freeShipping'=>FakerService::boolean(0.7),
            'taxCategory'=>1, // TODO: randomize
            'shippingCategory'=>1, // TODO: randomize
            'purchasable'=>FakerService::boolean(0.1),
            'promotable'=>FakerService::boolean(0.7)
        ];
    }

    /**
     * @throws \Exception
     */
    private function getVariants()
    {
        $n = $this->hasVariants ? random_int(2, 4) : 1;

        $res = [];

        for ($i = 1; $i <= $n; $i++) {
            $variant = [
                'title' => FakerService::words(1),
                'enabled'=>FakerService::boolean(0.1),
                'sku' => FakerService::uid(),
                'price' => FakerService::number(10, 100),
                'length'=>FakerService::number(20,50),
                'width' => FakerService::number(20, 50),
                'height' => FakerService::number(20, 50),
                'weight' => FakerService::number(300, 500),
                'stock'=>FakerService::number(50,100),
                'unlimitedStock'=>FakerService::boolean(0.8),
                'isDefault'=>$i===1,
                'minQuantity'=>FakerService::number(1,5),
                'maxQuantity'=>FakerService::number(10,20),
            ];

            foreach (FieldsList::$list as $fieldClass) {
                $factory = new FieldFactory($fieldClass);
                $fieldData = $factory->getFieldData();

                $feeder = $factory->getFeederClass();
                $variant[$fieldData['handle']] = $feeder->get();
            }

            $res[] = $variant;
        }

        return $res;
    }
}