<?php

namespace FourPaws\Search;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Elastica\Client;
use Elastica\Document;
use Elastica\Result;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Product;
use FourPaws\Search\Enum\DocumentType;
use FourPaws\Search\Model\HitMetaInfo;
use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;

class Factory
{
    const ENV_HOST = 'ELS_HOST';

    const ENV_PORT = 'ELS_PORT';

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $configParams
     *
     * @return Client
     * @throws RuntimeException
     */
    public function createElasticaClient(array $configParams = []): Client
    {
        $host = getenv(self::ENV_HOST);
        if (false === $host) {
            throw new EnvNotFoundException(self::ENV_HOST);
        }

        $port = getenv(self::ENV_PORT);
        if (false === $host) {
            throw new EnvNotFoundException(self::ENV_HOST);
        }

        $logger = null;
        foreach ($configParams as $paramPair) {
            foreach ($paramPair as $key => $value) {
                if ('log' === $key && true === $value) {
                    /** @var Logger $logger */
                    $logger = LoggerFactory::create('ElasticaClient', 'elasticsearch', false);
                }
            }
        }

        $client = new Client(['host' => $host, 'port' => $port], null, $logger);

        foreach ($configParams as $paramPair) {
            foreach ($paramPair as $key => $value) {
                $client->setConfigValue($key, $value);
            }
        }

        return $client;
    }

    /**
     * @param Product $product
     *
     * @return Document
     */
    public function makeProductDocument(Product $product)
    {
        return new Document(
            $product->getId(),
            $this->serializer->serialize(
                $product,
                'json',
                SerializationContext::create()->setGroups(['elastic'])
            ),
            DocumentType::PRODUCT
        );
    }

    /**
     * @param Brand $brand
     *
     * @return Document
     */
    public function makeBrandDocument(Brand $brand)
    {
        return new Document(
            $brand->getId(),
            $this->serializer->serialize(
                $brand,
                'json',
                SerializationContext::create()->setGroups(['elastic'])
            ),
            DocumentType::BRAND
        );
    }

    /**
     * @param Result $result
     *
     * @return Product
     * @throws RuntimeException
     */
    public function makeProductObject(Result $result): Product
    {
        if (DocumentType::PRODUCT !== $result->getType()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Ожидается тип документа `%s` , а получен `%s`',
                    DocumentType::PRODUCT,
                    $result->getType()
                )
            );
        }

        $product = $this->makeProductObjectFromArray($result->getSource());
        $product->withHitMetaInfo(HitMetaInfo::create($result));

        return $product;
    }

    /**
     * @param Result $result
     *
     * @return Brand
     * @throws RuntimeException
     */
    public function makeBrandObject(Result $result): Brand
    {
        if (DocumentType::BRAND !== $result->getType()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Ожидается тип документа `%s` , а получен `%s`',
                    DocumentType::BRAND,
                    $result->getType()
                )
            );
        }

        $brand = $this->makeBrandObjectFromArray($result->getSource());
        $brand->withHitMetaInfo(HitMetaInfo::create($result));

        return $brand;
    }

    /**
     * @param array $source
     *
     * @return Product
     * @throws RuntimeException
     */
    public function makeProductObjectFromArray(array $source) {
        $json = json_encode($source);

        $product = $this->serializer->deserialize(
            $json,
            Product::class,
            'json',
            DeserializationContext::create()->setGroups(['elastic'])
        );

        if (!($product instanceof Product)) {
            throw new RuntimeException('Ошибка десериализации продукта');
        }

        return $product;
    }

    /**
     * @param array $source
     *
     * @return Brand
     * @throws RuntimeException
     */
    public function makeBrandObjectFromArray(array $source) {
        $json = json_encode($source);

        $brand = $this->serializer->deserialize(
            $json,
            Brand::class,
            'json',
            DeserializationContext::create()->setGroups(['elastic'])
        );

        if (!($brand instanceof Brand)) {
            throw new RuntimeException('Ошибка десериализации бренда');
        }

        return $brand;
    }
}
