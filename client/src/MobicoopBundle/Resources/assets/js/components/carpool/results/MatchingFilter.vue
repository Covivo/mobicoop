<template>
  <v-content>
    <v-row
      justify="center"
      align="center"
    >
      <v-col
        cols="12"
        align-self="start"
        class="text-left"
      >
        <v-expansion-panels
          v-model="panel"
          accordion
        >
          <v-expansion-panel
            v-for="(currentPanel,i) in 1"
            :key="i"
            flat
          >
            <v-expansion-panel-header>
              {{ $t('filters') }} :
              <span
                v-if="chips"
                class="pl-4"
              >          
                <v-chip
                  v-for="chip in chips"
                  :key="chip.id"
                  close
                  @click:close="removeFilter(chip)"
                >
                  {{ chip.text }}
                </v-chip>
              </span>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row>
                <v-col cols="3">
                  <v-select
                    v-model="filters.order"
                    :items="itemsOrder"
                    :label="$t('select.order.label')"
                    outlined
                    dense
                    flat
                    :disabled="!filterEnabled.order"
                    @change="updateFilterDate"
                  />
                </v-col>
                <v-col cols="3">
                  <v-select
                    v-model="filters.time"
                    :items="itemsTime"
                    :label="$t('select.hour.label')"
                    outlined
                    dense
                    flat
                    :disabled="!filterEnabled.time"
                    @change="updateFilterTime"
                  />
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
  </v-content>
</template>

<script>
import { merge } from "lodash";
import Translations from "@translations/components/carpool/results/MatchingFilter.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingFilter.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props: {

  },
  data : function() {
    return {
      chips:[],
      filterEnabled:{
        "time":true,
        "order":true
      },
      itemsOrder: [
        {text:this.$t('select.order.date.increasing'),value:'ASC'},
        {text:this.$t('select.order.date.decreasing'),value:'DESC'}
      ],
      panel:null,
      filters:{
        order:null,
        time:null
      }
    };
  },
  computed:{
    itemsTime(){
      let hours = [];
      for (let i = 1; i < 24; i++) {
        hours.push({text:i+'h00',value:i+'h00'});
      }
      return hours;
    }
  },
  methods :{
    updateFilterDate(data){
      this.filterEnabled.date = false;
      this.chips.push({id:"order",text:this.$t('chips.date')+' : '+this.$t('chips.value.'+data),value:data});
      this.closePanel();
    },
    updateFilterTime(data){
      this.filterEnabled.time = false;
      this.chips.push({id:"time",text:this.$t('chips.hour')+' : '+data,value:data});
      this.closePanel();
    },
    removeFilter(item){
      this.filterEnabled[item.id] = true;
      this.filters[item.id] = null;
      this.chips.splice(this.chips.indexOf(item), 1);
    },
    closePanel(){
      this.panel = null;
    }
  }
};
</script>
<style lang="scss" scoped>
.v-expansion-panel{
  border:1px solid #E0E0E0;
  &::before{
    box-shadow:none;  
  }
}
</style>