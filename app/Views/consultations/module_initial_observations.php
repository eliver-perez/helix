<div class="flex flex-1">
    <div class="w-full">
        <textarea id="field-observaciones-iniciales" name="observaciones_iniciales" rows="5" class=" rounded-4 border-normal border-1 text-[15px] dark:bg-box-dark-up dark:border-box-dark-up px-[20px] py-[12px] outline-none placeholder:text-[#A0A0A0] text-body dark:text-subtitle-dark w-full focus:ring-primary focus:border-primary resize-none" placeholder="Captura el diagnostico del paciente..."><?= $consultation->data->initial_observations ?? ''; ?></textarea>
    </div>
</div>

<script>
    window.consultationModules = {
        ...window.consultationModules,
        initialObservations: true
    };
</script>