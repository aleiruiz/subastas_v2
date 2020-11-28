<?php

namespace App\Providers;

use App\Repositories\Admin\Eloquent\CategoryRepository;
use App\Repositories\Admin\Eloquent\CurrencyRepository;
use App\Repositories\Admin\Eloquent\LayoutRepository;
use App\Repositories\Admin\Eloquent\PaymentMethodRepository;
use App\Repositories\Admin\Eloquent\SliderRepository;
use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Repositories\Admin\Interfaces\CurrencyInterface;
use App\Repositories\Admin\Interfaces\LayoutInterface;
use App\Repositories\Admin\Interfaces\PaymentMethodInterface;
use App\Repositories\Admin\Interfaces\SliderInterface;
use App\Repositories\Core\Eloquent\ApplicationSettingRepository;
use App\Repositories\Core\Eloquent\CountryRepository;
use App\Repositories\Core\Eloquent\LanguageRepository;
use App\Repositories\Core\Eloquent\NavigationRepository;
use App\Repositories\Core\Eloquent\RoleRepository;
use App\Repositories\Core\Eloquent\StateRepository;
use App\Repositories\Core\Eloquent\SystemNoticeRepository;
use App\Repositories\Core\Interfaces\ApplicationSettingInterface;
use App\Repositories\Core\Interfaces\CountryInterface;
use App\Repositories\Core\Interfaces\LanguageInterface;
use App\Repositories\Core\Interfaces\NavigationInterface;
use App\Repositories\Core\Interfaces\RoleInterface;
use App\Repositories\Core\Interfaces\StateInterface;
use App\Repositories\Core\Interfaces\SystemNoticeInterface;
use App\Repositories\User\Eloquent\TransactionRepository;
use App\Repositories\User\Eloquent\WalletTransactionRepository;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Repositories\User\Eloquent\AddressRepository;
use App\Repositories\User\Eloquent\AuctionRepository;
use App\Repositories\User\Eloquent\BidRepository;
use App\Repositories\User\Eloquent\CommentRepository;
use App\Repositories\User\Eloquent\DisputeRepository;
use App\Repositories\User\Eloquent\KnowYourCustomerRepository;
use App\Repositories\User\Eloquent\NotificationRepository;
use App\Repositories\User\Eloquent\ProfileRepository;
use App\Repositories\User\Eloquent\SellerRepository;
use App\Repositories\User\Eloquent\UserRepository;
use App\Repositories\User\Eloquent\WalletRepository;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\CommentInterface;
use App\Repositories\User\Interfaces\DisputeInterface;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\ProfileInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Repositories\User\Interfaces\WalletTransactionInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(ApplicationSettingInterface::class, ApplicationSettingRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(NavigationInterface::class, NavigationRepository::class);
        $this->app->bind(SystemNoticeInterface::class, SystemNoticeRepository::class);
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(ProfileInterface::class, ProfileRepository::class);
        $this->app->bind(LanguageInterface::class, LanguageRepository::class);
        $this->app->bind(CountryInterface::class, CountryRepository::class);
        $this->app->bind(StateInterface::class, StateRepository::class);

        $this->app->bind(AuctionInterface::class, AuctionRepository::class);
        $this->app->bind(CategoryInterface::class, CategoryRepository::class);
        $this->app->bind(CurrencyInterface::class, CurrencyRepository::class);
        $this->app->bind(PaymentMethodInterface::class, PaymentMethodRepository::class);
        $this->app->bind(SellerInterface::class, SellerRepository::class);
        $this->app->bind(AddressInterface::class, AddressRepository::class);
        $this->app->bind(BidInterface::class, BidRepository::class);
        $this->app->bind(CommentInterface::class, CommentRepository::class);
        $this->app->bind(DisputeInterface::class, DisputeRepository::class);
        $this->app->bind(KnowYourCustomerInterface::class, KnowYourCustomerRepository::class);
        $this->app->bind(TransactionInterface::class, TransactionRepository::class);
        $this->app->bind(WalletInterface::class, WalletRepository::class);
        $this->app->bind(WalletTransactionInterface::class, WalletTransactionRepository::class);
        $this->app->bind(SliderInterface::class, SliderRepository::class);
        $this->app->bind(LayoutInterface::class, LayoutRepository::class);

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
