<template>
  <v-card>
    <v-toolbar
      color="primary"
    >
      <v-toolbar-title>
        {{ $t('detailTitle') }}
      </v-toolbar-title>
      
      <v-spacer />

      <v-btn 
        icon
        @click="$emit('close')"
      >
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-card-text>
      <!-- Date / seats / price -->
      <v-row
        align="center"
        dense
      >
        <!-- Date -->
        <v-col
          v-if="!regular"
          cols="5"
          class="title text-center"
        >
          {{ computedDate }}
        </v-col>

        <v-col
          v-else
          cols="5"
          class="title text-center"
        >
          <regular-days-summary 
            :mon-active="lResult.monCheck"
            :tue-active="lResult.tueCheck"
            :wed-active="lResult.wedCheck"
            :thu-active="lResult.thuCheck"
            :fri-active="lResult.friCheck"
            :sat-active="lResult.satCheck"
            :sun-active="lResult.sunCheck"
          />
        </v-col>

        <!-- Seats -->
        <v-col
          cols="3"
          class="title text-center"
        >
          {{ $tc('places', lResult.seats, { seats: lResult.seats }) }}
        </v-col>

        <!-- Price -->
        <v-col
          cols="4"
          class="title text-center"
        >
          {{ lResult.price ? lResult.price +'€' : '' }}
        </v-col>
      </v-row>

      <!-- Route / carpooler -->
      <v-row
        align="center"
        dense
      > 
        <!-- Route -->
        <v-col
          cols="8"
        >
          <v-row>
            <v-col>
              <v-journey
                :time="lResult.time || lResult.outwardTime ? true : false"
                :waypoints="waypoints"
              />
            </v-col>
          </v-row>
          <v-row 
            v-if="lResult.comment"
          >
            <v-col>
              <v-card
                outlined
                class="mx-auto"
              > 
                <v-card-text class="pre-formatted">
                  {{ lResult.comment }}
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-col>

        <!-- Carpooler -->
        <v-col
          cols="4"
        >
          <v-card>
            <!-- Avatar -->
            <v-img
              aspect-ratio="2"
              src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=BrownDark&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Default&skinColor=Light"
            />
            <v-card-title>
              <v-row
                dense
              >
                <v-col
                  class="text-center"
                >
                  {{ lResult.carpooler.givenName }} {{ lResult.carpooler.familyName.substr(0,1).toUpperCase()+"." }}
                </v-col>
              </v-row>
            </v-card-title>
            <v-card-text>
              <v-row
                dense
              >
                <v-col
                  cols="12"
                  class="text-center"
                >
                  {{ age }}
                </v-col>
                <v-col
                  cols="12"
                  class="text-center"
                >
                  {{ lResult.carpooler.telephone }}
                </v-col>
                
                <v-col  
                  cols="12"
                  class="text-center"
                >
                  <v-btn
                    color="primary"
                    :loading="contactLoading"
                    @click="contact"
                  >
                    <v-icon>
                      mdi-email
                    </v-icon>
                    {{ $t('contact') }}
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>

    <!-- Action buttons -->
    <v-card-actions>
      <div class="flex-grow-1" />

      <v-btn
        v-if="driver ^ passenger"
        color="secondary"
        @click="carpoolDialog = false"
      >
        {{ $t('carpool') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="secondary"
        @click="carpoolDialog = false"
      >
        {{ $t('carpoolAsDriver') }}
      </v-btn>

      <v-btn
        v-if="driver && passenger"
        color="secondary"
        @click="carpoolDialog = false"
      >
        {{ $t('carpoolAsPassenger') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import { merge } from "lodash";
import moment from "moment";
import Translations from "@translations/components/carpool/results/MatchingJourney.json";
import TranslationsClient from "@clientTranslations/components/carpool/results/MatchingJourney.json";
import VJourney from "@components/carpool/utilities/VJourney";
import RegularDaysSummary from "@components/carpool/utilities/RegularDaysSummary";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  components: {
    VJourney,
    RegularDaysSummary
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props: {
    result: {
      type: Object,
      default: null
    },
  },
  data : function() {
    return {
      locale: this.$i18n.locale,
      lResult: this.result,
      contactLoading: false
    }
  },
  computed: {
    driver() {
      return this.lResult && this.lResult.resultDriver ? true : false;
    },
    passenger() {
      return this.lResult && this.lResult.resultPassenger ? true : false;
    },
    regular() {
      return this.lResult && this.lResult.frequency == 2;
    },
    computedTime() {
      if (this.lResult && this.lResult.time) return moment.utc(this.lResult.time).format(this.$t("ui.i18n.time.format.hourMinute"));      
      return null;
    },
    computedDate() {
      if (this.lResult && this.lResult.date) return moment.utc(this.lResult.date).format(this.$t("ui.i18n.date.format.fullDate"));
      return null;
    },
    age() {
      return this.lResult ? moment().diff(moment([this.lResult.carpooler.birthDate]),'years')+' '+this.$t("birthYears") : ''
    },
    waypoints() {
      return this.lResult.resultPassenger ? this.lResult.resultPassenger.outward.waypoints : this.lResult.resultDriver.outward.waypoints;
    }
  },
  watch: {
    result(val) {
      this.lResult = val;
    }
  },
  methods: {
    contact() {
      this.contactLoading = true;
      let params = {
        "driver": this.lResult.resultDriver ? true : false,
        "passenger": this.lResult.resultPassenger ? true : false,
        "regular" : this.lResult.frequency == 2
      };
      // if the requester can be passenger, we take the informations from the resultPassenger outward item
      if (this.lResult.resultPassenger) {
        params.proposalId = this.lResult.resultPassenger.outward.proposalId;
        params.origin = this.lResult.resultPassenger.outward.origin;
        params.destination = this.lResult.resultPassenger.outward.destination;
        params.date = this.lResult.resultPassenger.outward.date;
        params.time = this.lResult.resultPassenger.outward.time;
        params.priceKm = this.lResult.resultPassenger.outward.priceKm;
      }
      this.$emit('contact', params);
    },
  }
};
</script>
<style>
</style>