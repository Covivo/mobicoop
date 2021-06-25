<template>
  <v-card
    outlined
  >
    <v-container>
      <v-row
        justify="start"
        align="center"
        dense
      >
        <!-- Role -->
        <v-col
          cols="2"
          align="center"
        >
          <v-icon
            v-if="journey.role!==2"
            color="primary"
            :size="journey.role!==3 ? '75' : '40'"
          >
            mdi-car
          </v-icon>

          <v-icon
            v-if="journey.role!==1"
            color="primary"
            :size="journey.role!==3 ? '75' : '40'"
          >
            mdi-walk
          </v-icon>
        </v-col>

        <!-- Detail -->
        <v-col
          cols="10"
        >
          <!-- Regular : summary of days -->
          <regular-planning-summary
            :mon-active="journey.days.mon"
            :tue-active="journey.days.tue"
            :wed-active="journey.days.wed"
            :thu-active="journey.days.thu"
            :fri-active="journey.days.fri"
            :sat-active="journey.days.sat"
            :sun-active="journey.days.sun"
            :outward-time="journey.outwardTime"
            :return-time="journey.returnTime"
            :return-trip="journey.type == 2"
          />

          <v-divider />

          <v-row
            align="center"
            dense
          >
            <!-- Route -->
            <v-col
              cols="12"
            >
              <v-row
                justify="center"
                align="center"
                dense
              >
                <!-- Origin -->
                <v-col
                  cols="5"
                >
                  {{ journey.origin }}
                </v-col>

                <!-- Icon -->
                <v-col
                  cols="2"
                >
                  <v-icon
                    size="60"
                  >
                    mdi-ray-start-end
                  </v-icon>
                </v-col>

                <!-- Destination -->
                <v-col
                  cols="5"
                >
                  {{ journey.destination }}
                </v-col>
              </v-row>
            </v-col>
          </v-row>

          <v-divider />

          <!-- Carpooler -->
          <v-row
            justify="center"
            align="center"
            dense
          >
            <!-- Carpooler identity -->
            <v-col
              cols="4"
              justify="left"
              align="center"
            >
              <v-list-item class="pa-0">
                <!--Carpooler data-->
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold">
                    {{ journey.username }}
                  </v-list-item-title>
                  <v-list-item-title>{{ $tc('age', journey.age ? journey.age : 0, { age: journey.age ? journey.age : 0}) }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-col>
            <v-col
              cols="8"
              justify="right"
              align="center"
            >
              <v-btn
                rounded
                color="secondary"
                large
                :loading="loadingBtn"
                @click="carpool"
              >
                <span>
                  {{ $t('carpool') }}
                </span>
              </v-btn>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-card>
</template>

<script>
import RegularPlanningSummary from "@components/carpool/utilities/RegularPlanningSummary";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/journey/JourneyResultRegular/";

export default {
  components: {
    RegularPlanningSummary
  },
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
  },
  props: {
    journey: {
      type: Object,
      default: () => {}
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return {
      loadingBtn:this.loading
    }
  },
  watch:{
    loading(){
      this.loadingBtn = this.loading;
    }
  },
  methods:{
    carpool(){
      this.$emit("carpool", {proposalId:this.journey.proposalId});
    }
  }
};
</script>