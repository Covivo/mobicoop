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
import Translations from "@translations/components/carpool/MatchingFilter.json";
import TranslationsClient from "@clientTranslations/components/carpool/MatchingFilter.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
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