<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'lucasbeley/test_clickeat';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'composer/package-versions-deprecated' => '1.11.99.1@7413f0b55a051e89485c5cb9f765fe24bb02a7b6',
  'doctrine/annotations' => '1.12.1@b17c5014ef81d212ac539f07a1001832df1b6d3b',
  'doctrine/cache' => '1.10.2@13e3381b25847283a91948d04640543941309727',
  'doctrine/collections' => '1.6.7@55f8b799269a1a472457bd1a41b4f379d4cfba4a',
  'doctrine/event-manager' => '1.1.1@41370af6a30faa9dc0368c4a6814d596e81aba7f',
  'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b',
  'doctrine/lexer' => '1.2.1@e864bbf5904cb8f5bb334f99209b48018522f042',
  'doctrine/mongodb-odm' => '2.2.0@fa967eadf9e226923a97b145e8018f94d6580f09',
  'doctrine/mongodb-odm-bundle' => '4.3.0@560191b6090e051272d832122f681aa3fcc5d8d5',
  'doctrine/persistence' => '2.1.0@9899c16934053880876b920a3b8b02ed2337ac1d',
  'friendsofphp/proxy-manager-lts' => 'v1.0.3@121af47c9aee9c03031bdeca3fac0540f59aa5c3',
  'jean85/pretty-package-versions' => '1.6.0@1e0104b46f045868f11942aea058cd7186d6c303',
  'laminas/laminas-code' => '4.0.0@28a6d70ea8b8bca687d7163300e611ae33baf82a',
  'laminas/laminas-eventmanager' => '3.3.1@966c859b67867b179fde1eff0cd38df51472ce4a',
  'laminas/laminas-zendframework-bridge' => '1.2.0@6cccbddfcfc742eb02158d6137ca5687d92cee32',
  'mongodb/mongodb' => '1.8.0@953dbc19443aa9314c44b7217a16873347e6840d',
  'phpdocumentor/reflection-common' => '2.2.0@1d01c49d4ed62f25aa84a747ad35d5a16924662b',
  'phpdocumentor/reflection-docblock' => '5.2.2@069a785b2141f5bcf49f3e353548dc1cce6df556',
  'phpdocumentor/type-resolver' => '1.4.0@6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0',
  'psr/cache' => '1.0.1@d11b50ad223250cf17b86e38383413f5a6764bf8',
  'psr/container' => '1.1.1@8622567409010282b7aeebe4bb841fe98b58dcaf',
  'psr/event-dispatcher' => '1.0.0@dbefd12671e8a14ec7f180cab83036ed26714bb0',
  'psr/log' => '1.1.3@0f73288fd15629204f9d42b7055f72dacbe811fc',
  'symfony/apache-pack' => 'v1.0.1@3aa5818d73ad2551281fc58a75afd9ca82622e6c',
  'symfony/cache' => 'v5.2.4@d15fb2576cdbe2c40d7c851e62f85b0faff3dd3d',
  'symfony/cache-contracts' => 'v2.2.0@8034ca0b61d4dd967f3698aaa1da2507b631d0cb',
  'symfony/config' => 'v5.2.4@212d54675bf203ff8aef7d8cee8eecfb72f4a263',
  'symfony/console' => 'v5.2.5@938ebbadae1b0a9c9d1ec313f87f9708609f1b79',
  'symfony/dependency-injection' => 'v5.2.5@be0c7926f5729b15e4e79fd2bf917cac584b1970',
  'symfony/deprecation-contracts' => 'v2.2.0@5fa56b4074d1ae755beb55617ddafe6f5d78f665',
  'symfony/doctrine-bridge' => 'v5.2.5@9e2c53f3e8f8a6ccecd80de5c2c8b71beeca7fc8',
  'symfony/dotenv' => 'v5.2.4@783f12027c6b40ab0e93d6136d9f642d1d67cd6b',
  'symfony/error-handler' => 'v5.2.4@b547d3babcab5c31e01de59ee33e9d9c1421d7d0',
  'symfony/event-dispatcher' => 'v5.2.4@d08d6ec121a425897951900ab692b612a61d6240',
  'symfony/event-dispatcher-contracts' => 'v2.2.0@0ba7d54483095a198fa51781bc608d17e84dffa2',
  'symfony/filesystem' => 'v5.2.4@710d364200997a5afde34d9fe57bd52f3cc1e108',
  'symfony/finder' => 'v5.2.4@0d639a0943822626290d169965804f79400e6a04',
  'symfony/flex' => 'v1.12.2@e472606b4b3173564f0edbca8f5d32b52fc4f2c9',
  'symfony/framework-bundle' => 'v5.2.5@4dae531503072a57cf26f7f4beb4c3ef8a061f8f',
  'symfony/http-client-contracts' => 'v2.3.1@41db680a15018f9c1d4b23516059633ce280ca33',
  'symfony/http-foundation' => 'v5.2.4@54499baea7f7418bce7b5ec92770fd0799e8e9bf',
  'symfony/http-kernel' => 'v5.2.5@b8c63ef63c2364e174c3b3e0ba0bf83455f97f73',
  'symfony/options-resolver' => 'v5.2.4@5d0f633f9bbfcf7ec642a2b5037268e61b0a62ce',
  'symfony/polyfill-intl-grapheme' => 'v1.22.1@5601e09b69f26c1828b13b6bb87cb07cddba3170',
  'symfony/polyfill-intl-normalizer' => 'v1.22.1@43a0283138253ed1d48d352ab6d0bdb3f809f248',
  'symfony/polyfill-mbstring' => 'v1.22.1@5232de97ee3b75b0360528dae24e73db49566ab1',
  'symfony/polyfill-php73' => 'v1.22.1@a678b42e92f86eca04b7fa4c0f6f19d097fb69e2',
  'symfony/polyfill-php80' => 'v1.22.1@dc3063ba22c2a1fd2f45ed856374d79114998f91',
  'symfony/property-access' => 'v5.2.4@3af8ed262bd3217512a13b023981fe68f36ad5f3',
  'symfony/property-info' => 'v5.2.4@7185bbc74e6f49c3f1b5936b4d9e4ca133921189',
  'symfony/routing' => 'v5.2.4@cafa138128dfd6ab6be1abf6279169957b34f662',
  'symfony/serializer' => 'v5.2.4@a285f474a72397ccbd384900abc968ffcb511dda',
  'symfony/service-contracts' => 'v2.2.0@d15da7ba4957ffb8f1747218be9e1a121fd298a1',
  'symfony/string' => 'v5.2.4@4e78d7d47061fa183639927ec40d607973699609',
  'symfony/var-dumper' => 'v5.2.5@002ab5a36702adf0c9a11e6d8836623253e9045e',
  'symfony/var-exporter' => 'v5.2.4@5aed4875ab514c8cb9b6ff4772baa25fa4c10307',
  'symfony/yaml' => 'v5.2.5@298a08ddda623485208506fcee08817807a251dd',
  'webmozart/assert' => '1.10.0@6964c76c7804814a842473e0c8fd15bab0f18e25',
  'dama/doctrine-test-bundle' => 'v6.5.0@a43f79239f446bb85ffa34e799878156a43b590b',
  'doctrine/common' => '3.1.1@2afde5a9844126bc311cd5f548b5475e75f800d3',
  'doctrine/data-fixtures' => '1.5.0@51d3d4880d28951fff42a635a2389f8c63baddc5',
  'doctrine/dbal' => '3.0.0@ee6d1260d5cc20ec506455a585945d7bdb98662c',
  'doctrine/doctrine-bundle' => '2.3.0@8b922578bdee2243a26202b13df795e170efaef8',
  'doctrine/sql-formatter' => '1.1.1@56070bebac6e77230ed7d306ad13528e60732871',
  'graviton/mongodb-fixtures-bundle' => 'v2.0.0@265101fb12b554b71bdb3b07fd796e5213cc2e3b',
  'monolog/monolog' => '2.2.0@1cb1cde8e8dd0f70cc0fe51354a59acad9302084',
  'symfony/browser-kit' => 'v5.2.4@3ca3a57ce9860318b20a924fec5daf5c6db44d93',
  'symfony/css-selector' => 'v5.2.4@f65f217b3314504a1ec99c2d6ef69016bb13490f',
  'symfony/debug-bundle' => 'v5.2.4@ec21bd26d24dab02ac40e4bec362b3f4032486e8',
  'symfony/dom-crawler' => 'v5.2.4@400e265163f65aceee7e904ef532e15228de674b',
  'symfony/monolog-bridge' => 'v5.2.5@8a330ab86c4bdf3983b26abf64bf85574edf0d52',
  'symfony/monolog-bundle' => 'v3.6.0@e495f5c7e4e672ffef4357d4a4d85f010802f940',
  'symfony/phpunit-bridge' => 'v5.2.4@9d85d900c1afe29138a0d5854505eb684bc3ac6d',
  'symfony/stopwatch' => 'v5.2.4@b12274acfab9d9850c52583d136a24398cdf1a0c',
  'symfony/translation-contracts' => 'v2.3.0@e2eaa60b558f26a4b0354e1bbb25636efaaad105',
  'symfony/twig-bridge' => 'v5.2.5@f3b6854071486b20d27ba5f3148cf9fba73f670f',
  'symfony/twig-bundle' => 'v5.2.4@5ebbb5f0e8bfaa0b4b37cb25ff97f83b18caf221',
  'symfony/web-profiler-bundle' => 'v5.2.4@4b28c24db64156ad892300be7fae1978bed075ce',
  'twig/twig' => 'v3.3.0@1f3b7e2c06cc05d42936a8ad508ff1db7975cdc5',
  'symfony/polyfill-ctype' => '*@0105a8ee24f20461a6457d36f3f1dcf34c7ccf7b',
  'symfony/polyfill-iconv' => '*@0105a8ee24f20461a6457d36f3f1dcf34c7ccf7b',
  'symfony/polyfill-php72' => '*@0105a8ee24f20461a6457d36f3f1dcf34c7ccf7b',
  'lucasbeley/test_clickeat' => 'dev-master@0105a8ee24f20461a6457d36f3f1dcf34c7ccf7b',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!class_exists(InstalledVersions::class, false) || !InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (class_exists(InstalledVersions::class, false) && InstalledVersions::getRawData()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }
}
