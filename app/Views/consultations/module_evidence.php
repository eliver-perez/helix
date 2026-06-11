<div class="sm:grid sm:grid-cols-12 max-sm:flex max-sm:flex-col gap-[25px]">
    <div class="lg:col-span-6">
        <h5 class="text-[16px] font-semibold leading-1 text-theme-gray dark:text-subtitle-dark">Antes:</h5>
        <label for="pic-evidencia-antes" id="pic-evidencia-antes-dropzone" class="flex flex-col items-center justify-center w-full sm:min-h-[280px] bg-white dark:bg-box-dark mb-[30px] mx-auto p-2.5 rounded-[10px] border-2 border-dashed border-[#c6d0dc] dark:border-box-dark-up hover:border-primary dark:hover:border-primary cursor-pointer transition-all duration-300 ease-linear">
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                <div class="text-[25px] text-light dark:text-subtitle-dark mb-[5px]">
                    <i class="uil uil-export"></i>
                </div>
                <span class="text-[15px] font-normal text-light-extra dark:text-subtitle-dark capitalize leading-[24px]">Arrastra una imagen</span>
                <p class="text-[16px] font-medium text-dark dark:text-title-dark mt-[5px]">o haz clic para<a class="text-primary" href="#"> Buscar </a>en tu equipo.</p>
            </div>
            <input id="pic-evidencia-antes" type="file" class="hidden" accept="image/png, image/jpeg">
        </label>
        <div class="sm:grid sm:grid-cols-12 max-sm:flex max-sm:flex-col gap-[25px] sector-imagenes-antes">
        </div>
    </div>

    <div class="lg:col-span-6">
        <h5 class="text-[16px] font-semibold leading-1 text-theme-gray dark:text-subtitle-dark">Después:</h5>
        <label for="pic-evidencia-despues" id="pic-evidencia-despues-dropzone" class="flex flex-col items-center justify-center w-full sm:min-h-[280px] bg-white dark:bg-box-dark mb-[30px] mx-auto p-2.5 rounded-[10px] border-2 border-dashed border-[#c6d0dc] dark:border-box-dark-up hover:border-primary dark:hover:border-primary cursor-pointer transition-all duration-300 ease-linear">
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                <div class="text-[25px] text-light dark:text-subtitle-dark mb-[5px]">
                    <i class="uil uil-export"></i>
                </div>
                <span class="text-[15px] font-normal text-light-extra dark:text-subtitle-dark capitalize leading-[24px]">Arrastra una imagen</span>
                <p class="text-[16px] font-medium text-dark dark:text-title-dark mt-[5px]">o haz clic para<a class="text-primary" href="#"> Buscar </a>en tu equipo.</p>
            </div>
            <input id="pic-evidencia-despues" type="file" class="hidden" accept="image/png, image/jpeg">
        </label>
        <div class="sm:grid sm:grid-cols-12 max-sm:flex max-sm:flex-col gap-[25px] sector-imagenes-despues"></div>
    </div>
</div>

<script>
    window.consultationModules = {
        ...window.consultationModules,
        evidence: true
    };
</script>