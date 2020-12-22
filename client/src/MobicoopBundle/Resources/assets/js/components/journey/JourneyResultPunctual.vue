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
          <v-row
            align="center"
            dense
          >
            <!-- Date and time -->
            <v-col
              cols="3"
            >
              <v-list-item two-line>
                <v-list-item-content>
                  <v-list-item-title class="text-h6 font-weight-bold">
                    {{ computedTime }}
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-body-2">
                    {{ computedDate }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-col>

            <!-- Route -->
            <v-col
              cols="9"
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
import moment from "moment";
import {messages_en, messages_fr} from "@translations/components/journey/JourneyResultPunctual/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'fr': messages_fr
    },
  },
  props: {
    journey: {
      type: Object,
      default: () => {}
    },
  },
  computed: {
    computedTime() {
      if (this.journey.time.date) {
        return moment(this.journey.time.date).format(this.$t("hourMinute"));  
      }
      return null;  
    },
    computedDate() {
      if (this.journey.fromDate.date) {
        return moment(this.journey.fromDate.date).format(this.$t("shortDate"));
      }
      return null;
    }
  },
  created() {
    moment.locale(this.$i18n.locale); // DEFINE DATE LANGUAGE
  },
};
</script>