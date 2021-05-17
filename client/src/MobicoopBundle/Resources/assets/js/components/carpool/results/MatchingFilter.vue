<template>
  <v-main>
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
                  :disabled="disabledFilters"
                  close
                  @click:close="removeFilter(chip)"
                >
                  {{ chip.text }}
                </v-chip>
              </span>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row>
                <!-- <v-col cols="3">
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
                <v-col cols="3"> -->
                <v-col cols="3">
                  <v-select
                    v-model="filters.filters.time"
                    :items="itemsTime"
                    :label="$t('select.hour.label')"
                    outlined
                    dense
                    flat
                    :disabled="!filterEnabled.time || disabledFilters"
                    @change="updateFilterTime"
                  />
                </v-col>
                <v-col
                  cols="1"
                  align="left"
                >
                  <v-tooltip
                    color="info"
                    right
                  >
                    <template v-slot:activator="{ on }">
                      <v-icon v-on="on">
                        mdi-help-circle-outline
                      </v-icon>
                    </template>
                    <span>{{ $t('select.hour.help') }}</span>
                  </v-tooltip>
                </v-col>
                <v-col cols="4">
                  <v-select
                    v-model="filters.filters.gender"
                    :items="itemsGender"
                    :label="$t('select.filter.gender.label')"
                    outlined
                    dense
                    flat
                    :disabled="!filterEnabled.gender || disabledFilters"
                    @change="updateFilterGender"
                  />
                </v-col>
                <v-col cols="4">
                  <v-select
                    v-if="filterEnabled.role && !disabledFilters"
                    v-model="filters.filters.role"
                    :items="itemsRole"
                    :label="$t('select.filter.role.label')"
                    outlined
                    dense
                    flat
                    @change="updateFilterRole"
                  />
                </v-col>
              </v-row>
              <v-row
                v-if="communities && communities.length>0"
              >
                <v-col cols="3">
                  <v-select
                    v-model="filters.filters.community"
                    :items="communities"
                    :label="$t('select.filter.community.label')"
                    outlined
                    dense
                    flat
                    :disabled="!filterEnabled.community || disabledFilters"
                    @change="updateFilterCommunity"
                  />
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
  </v-main>
</template>

<script>
import {messages_en, messages_fr, messages_eu} from "@translations/components/carpool/results/MatchingFilter/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    communities: {
      type: Array,
      default: null
    },
    disabledFilters: {
      type: Boolean,
      default: false
    },
    disableRole:{
      type: Boolean,
      default: false
    },
    defaultCommunityId:{
      type: Number,
      default:null
    },
    initFiltersChips:{
      type: Boolean,
      default: false
    }
  },
  data : function() {
    return {
      chips:[],
      filterEnabled:{
        "time":true,
        // "order":true,
        "role":!this.disableRole,
        "gender":true,
        "community":true
      },
      // itemsOrder: [
      //   {text:this.$t('select.order.date.increasing'),value:{criteria:'date',value:'ASC'}},
      //   {text:this.$t('select.order.date.decreasing'),value:{criteria:'date',value:'DESC'}}
      // ],
      itemsRole: [
        {text:this.$t('select.filter.role.driver.label'),value:this.$t('select.filter.role.driver.value')},
        {text:this.$t('select.filter.role.passenger.label'),value:this.$t('select.filter.role.passenger.value')},
        {text:this.$t('select.filter.role.both.label'),value:this.$t('select.filter.role.both.value')}
      ],
      itemsGender: [
        {text:this.$t('select.filter.gender.female.label'),value:this.$t('select.filter.gender.female.value')},
        {text:this.$t('select.filter.gender.male.label'),value:this.$t('select.filter.gender.male.value')},
        {text:this.$t('select.filter.gender.other.label'),value:this.$t('select.filter.gender.other.value')}
      ],
      panel:null,
      filters:{
        // order:null,
        filters:{
          // You can add here other filters
          time:null,
          role:null,
          gender:null,
          community:(this.defaultCommunityId) ? this.defaultCommunityId : null
        }
      }
    };
  },
  computed:{
    itemsTime(){
      let hours = [];
      for (let i = 0; i < 24; i++) {
        hours.push({text:i+'h00',value:i+'h00'});
      }
      return hours;
    }
  },
  watch:{
    disableRole(){
      this.filterEnabled['role'] = !this.disableRole;
    },
    initFiltersChips(){
      if(this.filters.filters.community) this.updateFilterCommunity(this.filters.filters.community,true);
    }
  },
  methods :{
    //   updateFilterDate(data){
    //   this.filterEnabled.order = false;
    //   this.chips.push({id:"order",text:this.$t('chips.date.label')+' : '+this.$t('chips.date.value.'+data.value),value:data.value});
    //   this.closePanel();
    //   this.$emit("updateFilters",this.filters);
    // },

    updateFilterTime(data){
      this.filterEnabled.time = false;
      this.chips.push({id:"time",text:data,value:data});
      this.closePanel();
      this.$emit("updateFilters",this.filters);
    },
    updateFilterRole(data){
      this.filterEnabled.role = false;
      this.chips.push({id:"role",text:this.$t('chips.role.value.'+data),value:data});
      this.closePanel();
      this.$emit("updateFilters",this.filters);
    },
    updateFilterGender(data){
      this.filterEnabled.gender = false;
      this.chips.push({id:"gender",text:this.$t('chips.gender.value.'+data),value:data});
      this.closePanel();
      this.$emit("updateFilters",this.filters);
    },
    updateFilterCommunity(data,noemitt=false){
      var name="";
      this.communities.forEach((result,key) => {
        if (result.value==data) name=result.text;
      });
      this.filterEnabled.community = false;
      this.chips.push({id:"community",text:this.$t('chips.community.label')+' : '+name,value:data});
      this.closePanel();
      if(!noemitt) this.$emit("updateFilters",this.filters);
    },
    removeFilter(item){
      this.filterEnabled[item.id] = true;
      // (item.id=="order") ? this.filters[item.id] = null : this.filters.filters[item.id] = null;
      this.filters.filters[item.id] = null;
      this.chips.splice(this.chips.indexOf(item), 1);
      this.$emit("updateFilters",this.filters);
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