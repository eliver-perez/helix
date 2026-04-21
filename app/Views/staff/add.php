<?php
    $title = "Agregar Personal";
    $section = "Personal";
    $subsection = "Agregar Personal";

    require_once __DIR__.'/../layout/title.php';
?>

               <div class="col-span-12 xl:col-span-6">
                  <div class="bg-white dark:bg-box-dark m-0 p-0 text-body dark:text-subtitle-dark text-[15px] rounded-10 relative">
                     <div class="px-[25px] text-dark dark:text-title-dark font-medium text-[17px] flex flex-wrap items-center justify-between max-sm:flex-col max-sm:h-auto border-b border-regular dark:border-box-dark-up">
                        <h1 class="mb-0 inline-flex items-center py-[16px] overflow-hidden whitespace-nowrap text-ellipsis text-[18px] font-semibold text-dark dark:text-title-dark capitalize">
                           Horizontal Form with icons
                        </h1>
                     </div>
                     <div class="p-[25px]">
                        <form>
                           <div class="flex flex-col pb-4 md:flex-row">
                              <label for="nameIcon" class="inline-flex items-center w-[178px] mb-2 text-sm font-medium capitalize text-dark dark:text-title-dark">Name</label>
                              <div class="flex items-center flex-1">
                                 <div class="w-full rounded-4 border-normal border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[15px] py-[12px] min-h-[50px] focus:ring-primary focus:border-primary gap-[12px]  flex items-center">
                                    <span class="inline-flex items-center text-sm text-light dark:text-subtitle-dark me-[8px]">
                                       <i class="uil uil-user text-[16px]"></i>
                                    </span>
                                    <input type="text" id="nameIcon" class="outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark w-full bg-transparent" placeholder="Duran Clayton" required autocomplete="username">
                                 </div>
                              </div>
                           </div>
                           <div class="flex flex-col pb-4 md:flex-row">
                              <label for="emailIcon" class="inline-flex items-center w-[178px] mb-2 text-sm font-medium capitalize text-dark dark:text-title-dark">Email</label>
                              <div class="flex items-center flex-1">
                                 <div class="w-full rounded-4 border-normal border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[15px] py-[12px] min-h-[50px] focus:ring-primary focus:border-primary gap-[12px]  flex items-center">
                                    <span class="inline-flex items-center text-sm text-light dark:text-subtitle-dark me-[8px]">
                                       <i class="uil uil-envelope text-[16px]"></i>
                                    </span>
                                    <input type="email" id="emailIcon" class="outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark w-full bg-transparent" placeholder="example@gmail.com" required autocomplete="username">
                                 </div>
                              </div>
                           </div>
                           <div class="flex flex-col pb-4 md:flex-row">
                              <label for="passwordIcon" class="inline-flex items-center w-[178px] mb-2 text-sm font-medium capitalize text-dark dark:text-title-dark">password</label>
                              <div class="flex flex-col flex-1 md:flex-row">
                                 <div class="w-full rounded-4 border-normal border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[15px] py-[12px] min-h-[50px] focus:ring-primary focus:border-primary gap-[12px] flex items-center">
                                    <span class="inline-flex items-center text-sm text-light dark:text-subtitle-dark me-[8px]">
                                       <i class="uil uil-lock text-[16px]"></i>
                                    </span>
                                    <input type="password" id="passwordIcon" class="outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark w-full bg-transparent" placeholder="*********" autocomplete="current-password" required>
                                 </div>
                              </div>
                           </div>
                           <div class="sm:ms-[178px] flex items-center gap-[15px] mt-[14px]">
                              <button type="button" class="px-[30px] h-[44px] text-body dark:text-subtitle-dark bg-regular dark:bg-box-dark-up border-regular dark:border-box-dark-up font-medium rounded-4 text-sm w-full sm:w-auto text-center inline-flex items-center justify-center capitalize transition-all duration-300 ease-linear hover:opacity-60" data-te-ripple-init="" data-te-ripple-color="light">Cancel</button>
                              <button type="submit" class="px-[30px] h-[44px] text-white bg-primary border-primary hover:bg-primary-hbr  font-medium rounded-4 text-sm w-full sm:w-auto text-center inline-flex items-center justify-center capitalize transition-all duration-300 ease-linear" data-te-ripple-init="" data-te-ripple-color="light">save</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>