<template>
  <div
    id="accessconfig"
    data-accessconfig-buttonname="Paramètres d’accessibilité"
    data-accessconfig-params="{ &quot;Prefix&quot; : &quot;a42-ac&quot;, &quot;ContainerClass&quot; : &quot;&quot;,&quot;ModalCloseButton&quot; : &quot;&quot;,&quot;ModalTitle&quot; : &quot;&quot;,&quot;FormFieldset&quot; : &quot;&quot;,&quot;FormFieldsetLegend&quot; : &quot;&quot;,&quot;FormRadio&quot; : &quot;&quot;}"
  />
</template>

<script>
import {messages_en, messages_fr, messages_eu} from "@translations/components/utilities/Accessibility/";
import '@css/page/home/accessconfig.scss';
import '@js/config/accessconfig.js';

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
  }
};
</script>