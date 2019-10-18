<template>
  <v-content>
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="6"
        align-self="start"
      >
        <!--TODO : REMOVE WHEN START CODING FILTER COMPONENT-->
        <!-- <v-combobox
            v-model="chips"
            :items="items"
            chips
            clearable
            multiple
            label="Filtres"
          >
            <template v-slot:selection="{ attrs, item, select, selected }">
              <v-chip
                v-bind="attrs"
                :input-value="selected"
                close
                @click="select"
                @click:close="remove(item)"
              >
                <strong>{{ item }}</strong>&nbsp;
              </v-chip>
            </template>
          </v-combobox> -->
      </v-col>
    </v-row>
  </v-content>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/carpool/results/MatchingFilter.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingFilter.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props: {

  },
  data : function() {
    return {
      
    };
  },
  methods :{
  }
};
</script>
<style>
</style>