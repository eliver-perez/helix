<?php
    $title = "Ajustes";
    $section = "Ajustes";

    require_once __DIR__.'/../layout/title.php';
?>

<div class="grid grid-cols-12 sm:gap-[25px] gap-y-[25px]">
    <div class="col-span-12 2xl:col-span-3">
        <div class="bg-white dark:bg-box-dark rounded-[10px] text-center">
            <div class="sm:px-[25px] px-[15px] pt-[25px] pb-[18px]">
                <div class="inline-block">
                    <img class="relative mb-0 ps-[27px]"
                        src="<?= asset('images/logos/logo-helix-120.png'); ?>"
                        alt="Logo">
                </div>
                <h3 class="mt-[28px] text-[18px] mb-[6px] font-medium text-dark dark:text-title-dark leading-[23px] hover:[&>a]:text-primary">
                    <label class="text-dark dark:text-title-dark" ><?= config('name'); ?></label>
                </h3>
            </div>
            <div class="border-t border-regular dark:border-box-dark-up">
            <nav class="px-[20px] pt-8 pb-5">
                <ul class="listItemActive" role="tablist" data-te-nav-ref>
                    <li role="presentation">
                        <a href="#tabs-accountSettings" data-te-toggle="pill" data-te-target="#tabs-accountSettings" role="tab" aria-controls="tabs-accountSettings" aria-selected="true" class="[&.active]:bg-primary/10 [&.active]:text-primary bg-transparent cursor-pointer dark:text-subtitle-dark duration-300 flex font-normal gap-[12px] items-center px-[10px] [&.active]:px-[20px] hover:px-[20px] py-[10px] rounded-[6px] text-[14px] text-light transition-[0.3s] active" data-te-nav-active>
                        <i class="uil uil-setting text-[18px]"></i>
                        <span>Ajustes Generales</span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabs-passwordSettings" data-te-toggle="pill" data-te-target="#tabs-passwordSettings" role="tab" aria-controls="tabs-passwordSettings" aria-selected="false" class="[&.active]:bg-primary/10 [&.active]:text-primary bg-transparent cursor-pointer dark:text-subtitle-dark duration-300 flex font-normal gap-[12px] items-center px-[10px] [&.active]:px-[20px] hover:px-[20px] hover:bg-primary/10 hover:text-primary py-[10px] rounded-[6px] text-[14px] text-light transition-[0.3s]">
                        <i class="uil uil-key-skeleton text-[18px]"></i>
                        <span>Cambio de Contraseñas</span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabs-socialProfile" data-te-toggle="pill" data-te-target="#tabs-socialProfile" role="tab" aria-controls="tabs-socialProfile" aria-selected="false" class="[&.active]:bg-primary/10 [&.active]:text-primary bg-transparent cursor-pointer dark:text-subtitle-dark duration-300 flex font-normal gap-[12px] items-center px-[10px] [&.active]:px-[20px] hover:px-[20px] hover:bg-primary/10 hover:text-primary py-[10px] rounded-[6px] text-[14px] text-light transition-[0.3s]">
                        <i class="uil uil-whatsapp text-[18px]"></i>
                        <span>WhatsaApp</span>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabs-notification" data-te-toggle="pill" data-te-target="#tabs-notification" role="tab" aria-controls="tabs-notification" aria-selected="false" class="[&.active]:bg-primary/10 [&.active]:text-primary bg-transparent cursor-pointer dark:text-subtitle-dark duration-300 flex font-normal gap-[12px] items-center px-[10px] [&.active]:px-[20px] hover:px-[20px] hover:bg-primary/10 hover:text-primary py-[10px] rounded-[6px] text-[14px] text-light transition-[0.3s]">
                        <i class="uil uil-bell text-[18px]"></i>
                        <span>Notificaciones</span>
                        </a>
                    </li>
                </ul>
            </nav>
            </div>
        </div>
    </div>
    <div class="col-span-12 2xl:col-span-9">
        <div class="hidden opacity-100 transition-opacity duration-150 ease-linear data-[te-tab-active]:block" id="tabs-accountSettings" role="tabpanel" aria-labelledby="tabs-accountSettings-tab" data-te-tab-active>
            <div class="bg-white dark:bg-box-dark rounded-10">
            <div class="py-[18px] px-[25px] text-dark dark:text-title-dark font-medium text-[17px] border-regular dark:border-box-dark-up border-b">
                <h1 class="mb-0 text-lg font-medium text-dark dark:text-title-dark">Ajustes Generales</h1>
                <span class="mb-0.5 text-light dark:text-subtitle-dark text-13 font-normal">Controla los ajustes de la plataforma</span>
            </div>
            <div class="px-[25px] pb-[50px] pt-[40px]">
                <div class="grid grid-cols-12 sm:gap-[25px] gap-y-[25px] content-center">
                </div>
            </div>
            </div>
        </div>
        <!-- end timeline -->
        <!-- activity -->
        <div class="hidden opacity-100 transition-opacity duration-150 ease-linear data-[te-tab-active]:block" id="tabs-passwordSettings" role="tabpanel" aria-labelledby="tabs-passwordSettings-tab">
            <div class="bg-white dark:bg-box-dark rounded-10">
            <div class="py-[18px] px-[25px] text-dark dark:text-title-dark font-medium text-[17px] border-regular dark:border-box-dark-up border-b">
                <h1 class="mb-0 text-lg font-medium text-dark dark:text-title-dark">Cambio de Contraseña</h1>
                <span class="mb-0.5 text-light dark:text-subtitle-dark text-13 font-normal">Modifica tu contraseña para acceder a la plataforma</span>
            </div>
            <div class="px-[25px] pb-[50px] pt-[40px]">
                <div class="grid grid-cols-12 sm:gap-[25px] gap-y-[25px] content-center">
                    <div class="col-span-12 xl:col-start-4 xl:col-span-6">
                        <form>
                        <div class="mb-6">
                            <label for="oldPassword" class="block mb-2 text-sm font-medium capitalize text-dark dark:text-title-dark">Password Anterior:</label>
                            <input type="text" id="oldPassword" class="w-full rounded-6 border-regular border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[20px] py-[12px] min-h-[50px] outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark focus:ring-primary focus:border-primary" placeholder="old password" autocomplete="off" required>
                        </div>
                        <div class="mb-6">
                            <label for="password" class="block mb-2 text-sm font-medium capitalize text-dark dark:text-title-dark">Nuevo Password:</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 end-0 flex items-center px-[15px]">
                                    <input class="hidden js-password-toggle" id="toggle" type="checkbox" autocompleted="">
                                    <label class=" rounded cursor-pointer text-light text-[15px] js-password-label dark:text-subtitle-dark" for="toggle"><i class="uil uil-eye-slash"></i></label>
                                </div>
                                <input type="password"
                                        id="password"
                                        class="js-password w-full rounded-6 border-regular border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[20px] py-[12px] min-h-[50px] outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark focus:ring-primary focus:border-primary"
                                        placeholder="new password"
                                        value=""
                                        autocomplete="off"
                                        required>
                            </div>
                            <p class="mt-[14px] text-light dark:text-subtitle-dark text-[13px]">
                                Mínimo 8 caracteres</p>
                        </div>
                        </form>
                        <div class="static flex flex-wrap items-center gap-[10px] sm:mt-[43px] mt-[27] ">
                        <button type="button" class="group text-[13px] border-normal font-semibold text-white dark:text-title-dark btn-outlined h-[37px] dark:border-box-dark-up sm:px-[20px] px-[15px] rounded-6 flex items-center gap-[5px] leading-[22px] hover:text-white hover:bg-primary-hbr bg-primary transition duration-300" data-te-ripple-init="" data-te-ripple-color="light">
                            Modificar Contraseña
                        </button>
                        <button type="button" class="group text-[13px] font-semibold text-theme-gray bg-normalBG dark:bg-box-dark-up dark:text-title-dark btn-outlined h-[37px] dark:border-box-dark-up sm:px-[20px] px-[15px] rounded-6 flex items-center gap-[5px] leading-[22px] hover:text-white hover:bg-dark transition duration-300 border-1 border-normal" data-te-ripple-init="" data-te-ripple-color="light">
                            Cancelar
                        </button>

                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <!-- end activity -->
        <!-- activity -->
        <div class="hidden opacity-100 transition-opacity duration-150 ease-linear data-[te-tab-active]:block" id="tabs-socialProfile" role="tabpanel" aria-labelledby="tabs-socialProfile-tab">
            <div class="bg-white dark:bg-box-dark rounded-10">
            <div class="py-[18px] px-[25px] text-dark dark:text-title-dark font-medium text-[17px] border-regular dark:border-box-dark-up border-b">
                <h1 class="mb-0 text-lg font-medium text-dark dark:text-title-dark">Integración WhatsApp</h1>
                <span class="mb-0.5 text-light dark:text-subtitle-dark text-13 font-normal">Integra WhatsApp para enviar notificaciones a tus clientes</span>
            </div>
            <div class="px-[25px] pb-[50px] pt-[40px]">
                <div class="grid grid-cols-12 sm:gap-[25px] gap-y-[25px] content-center">
                </div>
            </div>
            </div>
        </div>
        <!-- end activity -->
        <!-- activity -->
        <div class="hidden opacity-100 transition-opacity duration-150 ease-linear data-[te-tab-active]:block" id="tabs-notification" role="tabpanel" aria-labelledby="tabs-notification-tab">
            <div class="bg-white dark:bg-box-dark rounded-10">
            <div class="py-[18px] px-[25px] text-dark dark:text-title-dark font-medium text-[17px] border-regular dark:border-box-dark-up border-b">
                <h1 class="mb-0 text-lg font-medium text-dark dark:text-title-dark">Notificaciones</h1>
                <span class="mb-0.5 text-light dark:text-subtitle-dark text-13 font-normal">Elige las notificaciones que deseas recibir</span>
            </div>
            <div class="px-[25px] pb-[37px] pt-[30px]">
                <div class="bg-regularBG dark:bg-box-dark-up px-[25px] pb-[25px] pt-[15px] max-sm:px-[15px] rounded-[10px]">
                    <div class="flex items-center justify-between h-[50px]">
                        <h2 class="text-light dark:text-white/60 text-[15px] font-medium">Notificaciones</h2>
                        <button class="switch-trigger font-normal text-info text-[13px] border-none outline-none shadow-none bg-transparent">
                        Seleccionar todas
                        </button>
                    </div>
                    <div class="bg-white dark:bg-box-dark shadow-[0_5px_20px_rgba(173,181,217,0.05)] rounded-[10px]">
                        <ul>
                        <li class="flex items-center justify-between mb-0 px-[25px] py-[20px] border-b border-regular last:border-none dark:border-box-dark-up gap-[15px]">

                            <div>
                                <h4 class="mb-0.5 text-body dark:text-title-dark text-sm font-medium capitalize">Notificaciones Generales</h4>
                                <p class="mb-0 text-sm capitalize text-light dark:text-subtitle-dark">Obten las notificaciones generales generadas por la plataforma</p>
                            </div>
                            <label for="switch1" class="relative inline-flex items-center cursor-pointer">
                                <input id="switch1" name="switch1" type="checkbox" value="" class="sr-only peer switch-group">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                            </label>

                        </li>

                        </ul>
                    </div>
                </div>
                <div class="static flex flex-wrap items-center gap-[10px] sm:mt-[43px] mt-[24] ">
                    <button type="button" class="group text-[13px] border-normal font-semibold text-white dark:text-title-dark btn-outlined h-[37px] dark:border-box-dark-up sm:px-[20px] px-[15px] rounded-6 flex items-center gap-[5px] leading-[22px] hover:text-white hover:bg-primary-hbr bg-primary transition duration-300" data-te-ripple-init="" data-te-ripple-color="light">
                        Guardar Cambios
                    </button>
                    <button type="button" class="group text-[13px] font-semibold text-theme-gray bg-normalBG dark:bg-box-dark-up dark:text-title-dark btn-outlined h-[37px] dark:border-box-dark-up sm:px-[20px] px-[15px] rounded-6 flex items-center gap-[5px] leading-[22px] hover:text-white hover:bg-dark transition duration-300 border-1 border-normal" data-te-ripple-init="" data-te-ripple-color="light">
                        Cancelar
                    </button>

                </div>
            </div>
            </div>
        </div>
        <!-- end activity -->
        <!-- End: Tab Content -->
    </div>
    <!-- End Content -->
</div>

<script src="<?= asset('js/settings/index.js'); ?>"></script>

<script>
    var currentLink = '<?= base_url('settings'); ?>';
</script>