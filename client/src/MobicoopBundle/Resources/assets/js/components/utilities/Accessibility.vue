<template>
  <div
    id="accessconfig"
    data-accessconfig-buttonname="Paramètres d’accessibilité"
    data-accessconfig-params="{ &quot;Prefix&quot; : &quot;a42-ac&quot;, &quot;ContainerClass&quot; : &quot;&quot;,&quot;ModalCloseButton&quot; : &quot;&quot;,&quot;ModalTitle&quot; : &quot;&quot;,&quot;FormFieldset&quot; : &quot;&quot;,&quot;FormFieldsetLegend&quot; : &quot;&quot;,&quot;FormRadio&quot; : &quot;&quot;}"
  />
</template>

<script>
import { merge } from "lodash";
import {messages_fr, messages_en} from "@translations/components/utilities/Accessibility/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/utilities/Accessibility/";
import '@css/page/home/accessconfig.scss';
import '@js/config/accessconfig.js';
let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  props: {
  }
};
</script>