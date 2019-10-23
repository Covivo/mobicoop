<template>
  <div
    id="accessconfig"
    data-accessconfig-buttonname="Paramètres d’accessibilité"
    data-accessconfig-params="{ &quot;Prefix&quot; : &quot;a42-ac&quot;, &quot;ContainerClass&quot; : &quot;&quot;,&quot;ModalCloseButton&quot; : &quot;&quot;,&quot;ModalTitle&quot; : &quot;&quot;,&quot;FormFieldset&quot; : &quot;&quot;,&quot;FormFieldsetLegend&quot; : &quot;&quot;,&quot;FormRadio&quot; : &quot;&quot;}"
  />
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/utilities/Accessibility.json";
import TranslationsClient from "@clientTranslations/components/utilities/Accessibility.json";
import '@css/page/home/accessconfig.scss';
import '@js/config/accessconfig.js';
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  props: {
  }
};
</script>