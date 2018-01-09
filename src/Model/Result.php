<?php

namespace haringsrob\Icecat\Model;

/**
 * Parses the result data into a usable object.
 */
class Result implements ResultInterface
{

    /**
     * The actual data we fetched. To get the data you can use.
     *
     * @var \stdClass
     */
    private $data;

    /**
     * The images as an array.
     *
     * @var array
     */
    private $images = [];

    /**
     * Icecat Constructor.
     *
     * @todo: validation.
     *
     * @param \SimpleXMLElement $data
     */
    public function __construct($data)
    {
        $this->setBaseData($data);
    }

    /**
     * @inheritdoc
     */
    private function setBaseData($data)
    {
        $this->data = json_decode(json_encode($data));
    }

    /**
     * @inheritdoc
     */
    public function getBaseData()
    {
        return $this->data;
    }

    /**
     * Gets all attributes.
     */
    public function getAttributes()
    {
        return $this->getProductData()->{'@attributes'};
    }

    /**
     * Gets a specific attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function getAttribute($attribute)
    {
        return $this->getAttributes()->$attribute;
    }

    /**
     * Gets the supplier.
     *
     * @return string
     */
    public function getSupplier()
    {
        return $this->getProductData()->Supplier->{'@attributes'}->Name;
    }

    /**
     * Gets the long description.
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->getProductData()->ProductDescription->{'@attributes'}->LongDesc;
    }

    /**
     * Gets the short description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->getProductData()->ProductDescription->{'@attributes'}->ShortDesc;
    }

    /**
     * Gets the short summary description.
     *
     * @return string
     */
    public function ShortSummaryDescription()
    {
        return $this->getProductData()->SummaryDescription->ShortSummaryDescription;
    }


    /**
     * Gets the long summary description.
     *
     * @return string
     */
    public function LongSummaryDescription()
    {
        return $this->getProductData()->SummaryDescription->LongSummaryDescription;
    }


    /**
     * Gets the product category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->getProductData()->Category->Name->{'@attributes'}->Value;
    }

    /**
     * Gets an array of images.
     *
     * @return array
     */
    public function getImages()
    {
        if (empty($this->images)) {
            if ($this->productHasImageGallery()) {
                foreach ($this->getProductData()->ProductGallery->ProductPicture as $img) {
                    $attr = $img->{'@attributes'};
                    $this->images[]['high'] = $attr->Pic;
                    $this->images[]['low'] = $attr->LowPic;
                    $this->images[]['thumb'] = $attr->ThumbPic;
                }
            }
            if ($this->productHasMainImage()) {
                $this->images[]['high'] = $this->getProductData()->{'@attributes'}->HighPic;
                $this->images[]['low'] = $this->getProductData()->{'@attributes'}->LowPic;
                $this->images[]['thumb'] = $this->getProductData()->{'@attributes'}->ThumbPic;
            }
        }

        return $this->images;
    }

    /**
     * Checks if the product has an image gallery.
     *
     * @return bool
     */
    private function productHasImageGallery()
    {
        return !empty($this->getProductData()->ProductGallery);
    }

    private function productHasMainImage()
    {
        return !empty($this->getProductData()->{'@attributes'}->HighPic);
    }

    /**
     * Gets a specification by its identifier.
     *
     * @param integer $identifier
     *   The ID of the specification.
     *
     * @return mixed
     *   The content of the specification.
     */
    public function getSpecByIdentifier($identifier)
    {
        foreach ($this->getProductData()->ProductFeature as $feature) {
            if ($feature->{'@attributes'}->CategoryFeature_ID === $identifier) {
                return $feature->{'@attributes'}->Presentation_Value;
            }
        }
        return null;
    }

    /**
     * Gets a specification by name.
     *
     * @param string $specificationName
     *
     * @return mixed
     *   The content of the specification.
     */
    public function getSpecByName($specificationName)
    {
        foreach ($this->getProductData()->ProductFeature as $feature) {
            if (strtolower($feature->Feature->Name->{'@attributes'}->Value) === strtolower($specificationName)) {
                return $feature->{'@attributes'}->Presentation_Value;
            }
        }
        return null;
    }

    /**
     * Gets an array of specifications.
     *
     * @return array
     */
    public function getSpecs()
    {
        $specifications = [];

        foreach ($this->getProductData()->ProductFeature as $key => $feature) {
            $specifications[$key]['name'] = $feature->Feature->Name->{'@attributes'}->Value;
            $specifications[$key]['data'] = $feature->{'@attributes'}->Presentation_Value;
            $specifications[$key]['spec_id'] = $feature->{'@attributes'}->CategoryFeature_ID;
        }

        return $specifications;
    }

    /**
     * Gets all product data.
     *
     * @return \stdClass
     */
    public function getProductData()
    {
        return $this->data->Product;
    }

}
