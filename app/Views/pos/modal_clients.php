<div 
    data-te-modal-init
    class="fixed inset-0 z-[1055] hidden h-full w-full overflow-y-auto overflow-x-hidden outline-none"
    id="modal-clients"
    tabindex="-1"
    aria-hidden="true">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div 
            data-te-modal-dialog-ref 
            class="pointer-events-none relative w-full opacity-0 transition-all duration-300 ease-in-out h-70 min-[1280px]:max-w-[1100px]"
            >
            <div class="min-[576px]:shadow-[0_0.5rem_1rem_rgba(#000, 0.15)] pointer-events-auto relative flex w-full flex-col rounded-md border-none bg-white h-full bg-clip-padding text-current shadow-lg outline-none dark:bg-neutral-600">
                <div class="flex items-center justify-between flex-shrink-0 p-4 border-b border-opacity-100 rounded-t-md border-regular dark:border-box-dark-up">
                    <h5 class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200" id="modal-clients-title">
                        Clientes
                    </h5>
                    <button type="button" class="box-content border-none rounded-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none" data-te-modal-dismiss aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-dark dark:text-title-dark">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="relative flex-auto p-4" data-te-modal-body-ref>
                    <div class="sm:grid sm:grid-cols-12 max-sm:flex max-sm:flex-col">
                        <div class="col-span-12">
                            <div class="flex flex-col pb-4 md:flex-row">
                                <div class="flex items-center flex-1">
                                    <div class="w-full rounded-4 border-normal border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[15px] py-[12px] min-h-[50px] focus:ring-primary focus:border-primary gap-[12px]  flex items-center">
                                        <span class="inline-flex items-center text-sm text-light dark:text-subtitle-dark me-[8px]">
                                        <i class="uil uil-search text-[16px]"></i>
                                        </span>
                                        <input type="text" id="field-busqueda-clientes" name="busqueda_clientes" class="outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark w-full bg-transparent" placeholder="Busqueda" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12">
                            <div class="max-h-[455px] scrollbar overflow-y-auto">
                                <table id="table-clientes" class="min-w-full text-sm font-light text-start whitespace-nowrap product-container">
                                    <thead>
                                        <tr>
                                            <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-[25px] py-4 text-start text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden rounded-s-[4px]">
                                            Clave</th>
                                            <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-[25px] py-4 text-start text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden rounded-s-[4px]">
                                            Cliente</th>
                                            <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden">
                                            Domicilio</th>
                                            <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden">
                                            Teléfono</th>
                                            <th class="sticky top-0 capitalize bg-regularBG dark:bg-box-dark-up px-4 py-4 text-light dark:text-title-dark text-[14px] font-medium border-none before:hidden">
                                            E-Mail</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-box-dark">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end flex-shrink-0 gap-2 p-4 border-t-2 border-b border-opacity-100 rounded-b-md border-regular dark:border-box-dark-up">
                    <button type="button" id="btn-close-clients-modal" class="ml-1 inline-block rounded bg-danger px-6 pb-2 pt-2.5 text-14 font-medium capitalize leading-normal text-white  transition duration-150 ease-in-out hover:bg-primary-600 hover:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:bg-primary-600 focus:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] focus:outline-none focus:ring-0 active:bg-primary-700 active:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.3),0_4px_18px_0_rgba(59,113,202,0.2)] dark:shadow-[0_4px_9px_-4px_rgba(59,113,202,0.5)] dark:hover:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)] dark:focus:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)] dark:active:shadow-[0_8px_9px_-4px_rgba(59,113,202,0.2),0_4px_18px_0_rgba(59,113,202,0.1)]" data-te-modal-dismiss data-te-ripple-init data-te-ripple-color="light">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>