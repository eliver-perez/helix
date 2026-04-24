<?php
    $title = "Agenda";
    $section = "Agenda";

    require_once __DIR__.'/../layout/title.php';
?>

            <!-- Responsive Toggler -->
            <div class="flex items-center justify-center 4xl:hidden ssm:mb-[30px] mb-[15px]">
               <button id="chat-sidebar-selector" type="button" class=" text-danger text-sm font-semibold inline-flex justify-center items-center w-[40px] h-[40px] bg-white rounded-6 dark:bg-box-dark-up">
                  <i class="uil uil-align-left text-[24px]"></i>
               </button>
            </div>
            <div class="w-full mx-auto">
               <div class="flex flex-wrap mx-[-15px]">
                  <div class="4xl:w-[25%] w-full px-[15px] 4xl:mb-[30px]">
                     <div id="chat-sidebar-target" class="max-4xl:bg-white max-4xl:dark:bg-[#3a3b3c] 4xl:rounded-[10px] max-4xl:rounded-e-[10px] max-4xl:w-[280px] max-4xl:fixed max-4xl:z-[9999] max-4xl:start-0 [&.nav-open]:translate-x-0 max-4xl:top-0 max-4xl:h-full ltr:max-4xl:translate-x-[-280px] rtl:max-4xl:translate-x-[280px] max-4xl:shadow-lg duration-200 max-4xl:p-[15px] max-4xl:overflow-auto scrollbar">
                        <!-- Create New  Event -->
                        <button type="button" data-te-toggle="modal" data-te-target="#evenModal" class=" h-[50px] text-[14px] font-medium w-full rounded-[8px] mb-[25px] bg-primary border-primary text-white flex items-center justify-center gap-[6px] px-[30px]" data-te-ripple-init="" data-te-ripple-color="light">
                           <i class="uil uil-plus"></i>
                           Agendar Cita
                        </button>
                        <!-- Mini calendar -->
                        <div class="mb-[25px]">
                           <div class="[&>div]:w-full max-4xl:border-1 max-4xl:border-regular max-4xl:dark:border-box-dark-up max-4xl:rounded-6" id="mini-datepicker" data-date="10/24/2023"></div>
                        </div>
                        <!-- Calendar Events -->
                        <div class="bg-white max-4xl:border-1 max-4xl:border-regular max-4xl:dark:border-box-dark-up dark:bg-box-dark rounded-10 p-[25px]">
                           <div class="flex items-center justify-between mb-[18px] text-dark dark:text-title-dark">
                              <h3 class="font-medium text-[18px] text-dark dark:text-title-dark">
                                 My Calendars
                              </h3>
                           </div>
                           <ul>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-primary before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">family
                                    event</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-secondary before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">product
                                    launch</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-success before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">team
                                    meeting</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-info before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">ui/ux
                                    design team</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-danger before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">Project
                                    update</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-warning before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">Friends
                                    reunion</span>
                              </li>
                              <li class="flex items-center mb-[10px]">
                                 <span class="relative flex items-center text-sm capitalize before:-translate-y-2/4 before:absolute before:bg-primary before:h-2 before:rounded-full before:top-1/2 before:w-2 dark:text-subtitle-dark ltr:before:left-0 ps-4 rtl:before:right-0 text-body">Development
                                    meeting</span>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <div class="4xl:w-[75%] w-full px-[15px] mb-[30px]">
                     <div id='full-calendar' class='relative bg-white main-calendar dark:bg-box-dark rounded-10 p-[25px] overflow-x-auto scrollbar'>
                     </div>
                  </div>
               </div>
            </div>

<script src="<?= asset('js/appointments/index.js'); ?>"></script>

<script>
    var currentLink = '<?= base_url('appointments'); ?>';
</script>