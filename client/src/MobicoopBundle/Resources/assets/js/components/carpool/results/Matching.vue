<template>
  <div>
    <v-container fluid>
      <v-row justify="center">
        <v-col
          cols="8"
          md="8"
          xl="6"
          align="center"
        >
          <h1>
            {{
              $t("title", { cityA: displayOrigin, cityB: displayDestination })
            }}
          </h1>

          <!-- Matching header -->
          <matching-header
            v-if="!lProposalId && !lExternalId"
            :origin="origin"
            :destination="destination"
            :date="date"
            :time="time"
            :regular="regular"
          />
          <!-- Matching filter -->
          <matching-filter
            :communities="communities"
            :disabled-filters="loading"
            :disable-role="!includePassenger"
            :default-community-id="lCommunityId"
            :init-filters-chips="initFiltersChips"
            @updateFilters="updateFilters"
          />

          <!-- Number of matchings -->
          <v-row
            justify="center"
            align="center"
          >
            <v-col
              v-if="!loading && !loadingExternal"
              :cols="!newSearch ? 8 : 12"
              class="text-left"
            >
              <p>
                {{
                  $tc("matchingNumber", numberOfResults, {
                    number: numberOfResults
                  })
                }}
              </p>
              <p
                v-if="numberOfResults == 0 && !regular"
                class="font-weight-bold"
              >
                {{ $t("AskNewSearch") }}
              </p>
            </v-col>
            <v-col
              v-else
              cols="12"
            >
              <v-alert
                text
                color="success"
              >
                {{ loadingText }}
                <v-progress-linear
                  indeterminate
                  rounded
                />
              </v-alert>
            </v-col>
            <v-col
              v-if="!loading && !loadingExternal && !newSearch"
              cols="4"
              align="end"
            >
              <v-btn
                v-if="displayNewSearch"
                rounded
                color="secondary"
                @click="startNewSearch()"
              >
                {{ $t("newSearch") }}
              </v-btn>
            </v-col>
          </v-row>

          <!-- Include passengers switch -->
          <v-row v-if="displayNewSearch">
            <v-col
              v-if="!loading"
              cols="12"
              class="text-left"
            >
              <v-switch
                v-if="role != 3 && externalId == ''"
                v-model="includePassenger"
                class="ma-2"
                :label="$t('includePassengers')"
              />
              <v-alert
                v-else-if="externalId == ''"
                class="accent white--text"
                dense
                dismissible
              >
                {{ $t("alsoIncludePassengers") }}
              </v-alert>
            </v-col>
          </v-row>

          <!-- New search -->
          <v-row v-if="newSearch">
            <v-col cols="12">
              <search
                :geo-search-url="geoSearchUrl"
                :geo-complete-results-order="geoCompleteResultsOrder"
                :geo-complete-palette="geoCompletePalette"
                :geo-complete-chip="geoCompleteChip"
                :user="user"
                :regular="regular"
                :hide-publish="true"
                :punctual-date-optional="punctualDateOptional"
                :results="true"
                :default-destination="destination"
                :default-origin="origin"
                :default-outward-date="date"
                :default-outward-time="time"
                :date-time-picker="dateTimePicker"
              />
            </v-col>
          </v-row>

          <!-- Tabs : platform results, external results, public transport results -->
          <v-tabs
            v-if="displayTab"
            v-model="modelTabs"
          >
            <!-- Platform results tab -->
            <v-tab href="#carpools">
              <v-badge
                color="primary"
                :content="nbCarpoolPlatform"
                icon="mdi-timer-sand"
              >
                {{ $t("tabs.carpools", { platform: platformName }) }}
              </v-badge>
            </v-tab>
            <!-- External results tab -->
            <v-tab
              v-if="externalRdexJourneys"
              href="#otherCarpools"
              @change="externalRdexJourneysTabChange()"
            >
              <v-badge
                color="primary"
                :content="nbCarpoolOther"
                :icon="
                  loadingExternal ? 'mdi-timer-sand' : 'mdi-cursor-default'
                "
              >
                {{ $t("tabs.otherCarpools") }}
              </v-badge>
            </v-tab>
            <!-- Public transport results tab -->
            <v-tab
              v-if="ptSearch"
              href="#ptSearch"
              @change="ptSearchTabChange()"
            >
              <v-badge
                color="primary"
                :content="nbPtResults"
                :icon="
                  loadingPtResults ? 'mdi-timer-sand' : 'mdi-cursor-default'
                "
              >
                {{ $t("tabs.ptresults") }}
              </v-badge>
            </v-tab>
          </v-tabs>
          <!-- Tabs items  -->
          <v-tabs-items v-model="modelTabs">
            <!-- Platform results tab item -->
            <v-tab-item value="carpools">
              <div v-if="results && results.length > 0">
                <!-- Trajets pour la date demandée -->
                <v-row v-if="dateResults.length > 0">
                  <v-col>
                    <matching-results
                      :results="dateResults"
                      :nb-results="
                        isNaN(nbCarpoolPlatform) ? 0 : nbCarpoolPlatform
                      "
                      :distinguish-regular="distinguishRegular"
                      :user="user"
                      :loading-prop="loading"
                      :page="page"
                      :age-display="ageDisplay"
                      :birthdate-display="birthdateDisplay"
                      @carpool="carpool"
                      @loginOrRegister="loginOrRegister"
                      @paginate="paginate"
                    />
                  </v-col>
                </v-row>
                <div v-else>
                  <EmptyResult
                    :date="date"
                    @publishAd="handlePublishAd"
                  />
                </div>
                <!-- Trajets pour les jours suivants -->
                <div v-if="true">
                  <v-row>
                    <v-col align="left">
                      <h2 class="mt-15">
                        {{ $t("futureAds") }}
                      </h2>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col>
                      <matching-results
                        :results="futureResults"
                        :nb-results="
                          isNaN(nbCarpoolPlatform) ? 0 : nbCarpoolPlatform
                        "
                        :distinguish-regular="distinguishRegular"
                        :user="user"
                        :loading-prop="loading"
                        :page="page"
                        :age-display="ageDisplay"
                        :birthdate-display="birthdateDisplay"
                        @carpool="carpool"
                        @loginOrRegister="loginOrRegister"
                        @paginate="paginate"
                      />
                    </v-col>
                  </v-row>
                </div>
              </div>
              <div v-else-if="!loading">
                <EmptyResult
                  :date="date"
                  @publishAd="handlePublishAd"
                />
              </div>
            </v-tab-item>
            <!-- External results tab item -->
            <v-tab-item
              v-if="externalRdexJourneys"
              value="otherCarpools"
            >
              <matching-results
                :results="externalRDEXResults"
                :distinguish-regular="distinguishRegular"
                :user="user"
                :loading-prop="loadingExternal"
                :external-rdex-journeys="externalRdexJourneys"
                :platform-name="platformName"
                :carpool-standard-booking-enabled="
                  carpoolStandardBookingEnabled
                "
                :carpool-standard-messaging-enabled="
                  carpoolStandardMessagingEnabled
                "
                @loginOrRegister="loginOrRegister"
                @booking="booking"
                @carpoolStandardContact="carpoolStandardContact"
              />
            </v-tab-item>
            <!-- Public transport results tab item -->
            <v-tab-item
              v-if="ptSearch"
              value="ptSearch"
            >
              <MatchingPTResults
                :pt-results="ptResults"
                :loading-pt-results="loadingPtResults"
              />
            </v-tab-item>
          </v-tabs-items>
        </v-col>
      </v-row>
      <!-- <v-row
        v-if="user && !loading"
        justify="center"
      >
        <v-col
          cols="8"
          md="8"
          xl="6"
          align="center"
        >
          <v-card
            style="height: 100%;"
            flat
          >
            <v-card-text class="pb-0">
              <v-img
                v-if="nbCarpoolPlatform == '-' && displayLogoNoResult"
                max-height="90px"
                contain
                :src="$t('logoNoResult')"
                alt="no result logo"
              />
              <p
                v-if="nbCarpoolPlatform == '-'"
                class="text-h6"
                v-html="
                  $t('saveSearch.noAd', {
                    cityA: displayOrigin,
                    cityB: displayDestination
                  })
                "
              />
              <p class="black--text">
                {{ $t("saveSearch.message") }}
              </p>
            </v-card-text>
            <v-card-actions class="pt-0">
              <v-col cols="12">
                <v-btn
                  color="primary"
                  rounded
                  @click="saveSearch()"
                >
                  <v-icon color="white">
                    mdi-bell
                  </v-icon>{{ $t("saveSearch.button.label") }}
                </v-btn>
              </v-col>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row> -->
    </v-container>

    <!-- carpool dialog -->
    <v-dialog
      v-model="carpoolDialog"
      max-width="900"
    >
      <matching-journey
        :result="result"
        :user="user"
        :reset-step="resetStepMatchingJourney"
        :profile-summary-refresh="profileSummaryRefresh"
        :fraud-warning-display="fraudWarningDisplay"
        :age-display="ageDisplay"
        :refresh-map="refreshMapMatchingJourney"
        :carpool-settings-display="carpoolSettingsDisplay"
        @close="carpoolDialog = false"
        @contact="contact"
        @carpool="launchCarpool"
        @resetStepMatchingJourney="resetStepMatchingJourney = false"
        @profileSummaryRefresh="refreshProfileSummary"
        @mapRefreshed="mapRefreshed"
      />
    </v-dialog>

    <!-- booking dialog -->
    <v-dialog
      v-model="bookingDialog"
      max-width="500"
    >
      <booking-journey
        :result="result"
        :user="user"
        @close="bookingDialog = false"
        @booking="launchExternalBooking"
      />
    </v-dialog>

    <!-- carpoolStandard contact dialog -->
    <v-dialog
      v-model="carpoolStandardContactDialog"
      width="80%"
      min-height="500px"
    >
      <v-card>
        <v-card-title class="headline grey lighten-2">
          {{ $t("carpoolStandard.dialog.title") }}
        </v-card-title>

        <v-card-text>
          <p>
            {{
              $t("carpoolStandard.dialog.intro", {
                origin: result ? result.externalOperator : null,
                platform: platformName
              })
            }}.
          </p>
          <p>
            {{ $t("carpoolStandard.dialog.instructions") }}
          </p>
        </v-card-text>
        <v-card-text>
          <v-textarea
            v-model="carpoolStandardContactMessage"
            name="input-7-1"
            label="votre message"
            rows="5"
          />
          <p class="text-right">
            <v-btn
              rounded
              color="primary"
              :loading="loadingCarpoolStandardContact"
              @click="launchExternalBooking(result)"
            >
              {{ $t("carpoolStandard.dialog.send") }}
            </v-btn>
          </p>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="error"
            text
            @click="
              (carpoolStandardContactDialog = false),
              (carpoolStandardContactMessage = null)
            "
          >
            {{ $t("carpoolStandard.dialog.cancel") }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- login or register dialog -->
    <LoginOrRegisterFirst
      :id="lProposalId"
      :show-dialog="loginOrRegisterDialog"
      type="proposal"
      @closeLoginOrRegisterDialog="loginOrRegisterDialog = false"
    />
  </div>
</template>
<script>
import maxios from "@utils/maxios";
import {
  messages_en,
  messages_fr,
  messages_eu,
  messages_nl
} from "@translations/components/carpool/results/Matching/";
import {
  messages_client_en,
  messages_client_fr,
  messages_client_eu,
  messages_client_nl
} from "@clientTranslations/components/carpool/results/Matching/";
import MatchingHeader from "@components/carpool/results/MatchingHeader";
import MatchingFilter from "@components/carpool/results/MatchingFilter";
import MatchingResults from "@components/carpool/results/MatchingResults";
import MatchingJourney from "@components/carpool/results/MatchingJourney";
import EmptyResult from "@components/carpool/results/EmptyResult";
import BookingJourney from "@components/carpool/results/BookingJourney";
import MatchingPTResults from "@components/carpool/results/publicTransport/MatchingPTResults";
import LoginOrRegisterFirst from "@components/utilities/LoginOrRegisterFirst";
import Search from "@components/carpool/search/Search";
import formData from "../../../utils/request";
import { merge } from "lodash";
import moment from "moment";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  components: {
    MatchingHeader,
    MatchingFilter,
    MatchingResults,
    MatchingJourney,
    Search,
    MatchingPTResults,
    LoginOrRegisterFirst,
    BookingJourney,
    EmptyResult
  },
  i18n: {
    messages: {
      en: MessagesMergedEn,
      nl: MessagesMergedNl,
      fr: MessagesMergedFr,
      eu: MessagesMergedEu
    }
  },
  props: {
    // proposal Id if Matching results after an ad post
    proposalId: {
      type: Number,
      default: null
    },
    // external Id after external search
    externalId: {
      type: String,
      default: null
    },
    // limit the result to the given matching proposal Id
    // NOT USED YET
    targetProposalId: {
      type: String,
      default: null
    },
    origin: {
      type: Object,
      default: null
    },
    originLiteral: {
      type: String,
      default: null
    },
    destination: {
      type: Object,
      default: null
    },
    destinationLiteral: {
      type: String,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    time: {
      type: String,
      default: null
    },
    user: {
      type: Object,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    communityId: {
      type: Number,
      default: null
    },
    distinguishRegular: {
      type: Boolean,
      default: false
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    externalRdexJourneys: {
      type: Boolean,
      default: false
    },
    platformName: {
      type: String,
      default: ""
    },
    defaultRole: {
      type: Number,
      default: 3
    },
    ptSearch: {
      type: Boolean,
      default: false
    },
    fraudWarningDisplay: {
      type: Boolean,
      default: false
    },
    ageDisplay: {
      type: Boolean,
      default: false
    },
    displayLogoNoResult: {
      type: Boolean,
      default: false
    },
    geoCompleteResultsOrder: {
      type: Array,
      default: null
    },
    geoCompletePalette: {
      type: Object,
      default: () => ({})
    },
    geoCompleteChip: {
      type: Boolean,
      default: false
    },
    carpoolSettingsDisplay: {
      type: Boolean,
      default: true
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
    birthdateDisplay: {
      type: Boolean,
      default: false
    },
    carpoolStandardBookingEnabled: {
      type: Boolean,
      default: false
    },
    carpoolStandardMessagingEnabled: {
      type: Boolean,
      default: false
    },
    dateTimePicker: {
      type: Boolean,
      default: false
    },
    defaultIncludePassenger: {
      type: Boolean,
      default: false
    }
  },
  data: function() {
    return {
      locale: localStorage.getItem("X-LOCALE"),
      carpoolDialog: false,
      bookingDialog: false,
      loginOrRegisterDialog: false,
      carpoolStandardContactDialog: false,
      carpoolStandardContactMessage: null,
      loadingCarpoolStandardContact: false,
      results: null,
      searchId: null,
      externalRDEXResults: null,
      result: null,
      ptResults: null,
      loading: true,
      loadingStep: -1,
      loadingText: "",
      loadingInterval: null,
      loadingDelay: 3000,
      loadingExternal: false,
      lProposalId: this.proposalId ? this.proposalId : null,
      lExternalId: this.externalId ? this.externalId : null,
      loadingPtResults: false,
      lOrigin: null,
      lDestination: null,
      filters: null,
      newSearch: false,
      modelTabs: "carpools",
      nbCarpoolPlatform: 0,
      nbCarpoolOther: 0,
      nbPtResults: 0,
      role: this.defaultRole,
      includePassenger: this.defaultIncludePassenger,
      displayNewSearch: true,
      initFiltersChips: false,
      lCommunityId: this.communityId,
      lCommunityIdBak: this.communityId,
      resetStepMatchingJourney: false,
      profileSummaryRefresh: false,
      page: 1,
      refreshMapMatchingJourney: false,
      ptSearchLoaded: false,
      externalJourneysLoaded: false
    };
  },
  computed: {
    numberOfResults() {
      let numberOfResults = 0;
      !isNaN(this.nbCarpoolPlatform)
        ? (numberOfResults = numberOfResults + this.nbCarpoolPlatform)
        : 0;
      !isNaN(this.nbCarpoolOther)
        ? (numberOfResults = numberOfResults + this.nbCarpoolOther)
        : 0;
      return numberOfResults;
    },
    dateResults() {
      if (!this.results) return [];
      // Results for the requested date
      const requestedDate = this.date ? moment(this.date) : moment();
      const dayStart = requestedDate.clone().startOf("day");
      const dayEnd = requestedDate.clone().endOf("day");

      return this.results.filter(result => {
        const dateToUse =
          result.frequency === 2 ? result.startDate : result.date;

        if (!dateToUse || !result.time) return false;
        const resultDateTime = moment(
          `${dateToUse} ${result.time}`,
          "YYYY-MM-DD HH:mm"
        );

        return resultDateTime.isBetween(dayStart, dayEnd, null, "[]");
      });
    },
    futureResults() {
      if (!this.results) return [];
      // Results for dates after the requested date
      const requestedDate = this.date ? moment(this.date) : moment();
      const dayEnd = requestedDate.clone().endOf("day");

      return this.results.filter(result => {
        const dateToUse =
          result.frequency === 2 ? result.startDate : result.date;

        if (!dateToUse || !result.time) return false;
        const resultDateTime = moment(
          `${dateToUse} ${result.time}`,
          "YYYY-MM-DD HH:mm"
        );

        return resultDateTime.isAfter(dayEnd);
      });
    },

    communities() {
      if (!this.results) return null;
      let communities = [];
      this.results.forEach((result, key) => {
        if (result.communities) {
          for (let key in result.communities) {
            if (communities.indexOf(result.communities[key].name) == -1) {
              communities.push({
                text: result.communities[key].name,
                value: key
              });
            }
          }
        }
      });
      return communities;
    },
    displayTab() {
      return this.externalRdexJourneys || this.ptSearch ? true : false;
    },
    displayOrigin() {
      if (this.lOrigin && this.lOrigin.name) {
        return this.$t(this.lOrigin.name);
      } else if (this.lOrigin && this.lOrigin.addressLocality) {
        return this.$t(this.lOrigin.addressLocality);
      } else {
        return this.lOrigin ? this.$t(this.lOrigin) : "";
      }
    },
    displayDestination() {
      if (this.lDestination && this.lDestination.name) {
        return this.$t(this.lDestination.name);
      } else if (this.lDestination && this.lDestination.addressLocality) {
        return this.$t(this.lDestination.addressLocality);
      } else {
        return this.lDestination ? this.$t(this.lDestination) : "";
      }
    }
  },
  watch: {
    includePassenger() {
      if (this.includePassenger) {
        this.role = 3;
        this.lProposalId = null;
      } else {
        this.role = 2;
      }
      this.search();
    },
    communities() {
      this.initFiltersChips = true;
    },
    lCommunityId() {
      this.lCommunityIdBak = this.lCommunityId;
    }
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
    if (this.includePassenger) {
      this.role = 3;
    }
    if (this.proposalId) this.displayNewSearch = false;
    this.search();
    this.lOrigin = this.origin;
    this.lDestination = this.destination;
  },
  methods: {
    carpool(carpool) {
      this.result = carpool;
      // open the dialog
      this.refreshMapMatchingJourney = true;
      this.carpoolDialog = true;
      this.resetStepMatchingJourney = true;
      this.profileSummaryRefresh = true;
    },
    booking(booking) {
      this.result = booking;
      // open the dialog
      this.bookingDialog = true;
    },
    carpoolStandardContact(carpoolStandardContact) {
      this.result = carpoolStandardContact;
      // open the dialog
      this.carpoolStandardContactDialog = true;
    },
    loginOrRegister(carpool) {
      this.result = carpool;
      // open the dialog
      this.loginOrRegisterDialog = true;
    },
    handlePublishAd() {
      const adSettings = {
        origin: JSON.stringify(this.origin),
        destination: JSON.stringify(this.destination),
        regular: this.regular,
        date: this.date ? this.date : null,
        time: this.time ? this.time : null
      };
      localStorage.setItem("adSettings", JSON.stringify(adSettings));
      window.location.href = this.$t("publishAd");
    },
    paginate(page) {
      this.page = page;
      this.search();
    },

    setLoadingStep() {
      this.loadingStep++;
      if (this.loadingStep > 2) this.loadingStep = 0;
      switch (this.loadingStep) {
      case 1:
        this.loadingText = this.$t("search2");
        break;
      case 2:
        this.loadingText = this.$t("search3");
        break;
      default:
        this.loadingText = this.$t("search");
        break;
      }
    },
    search() {
      // if a proposalId is provided, we load the proposal results
      if (this.lProposalId) {
        this.loading = true;
        this.setLoadingStep();
        this.loadingInterval = setInterval(
          this.setLoadingStep,
          this.loadingDelay
        );
        if (this.filters === null) {
          this.filters = {
            page: this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          filters: this.filters
        };
        maxios
          .post(
            this.$t("proposalUrl", { id: Number(this.lProposalId) }),
            postParams,
            {
              headers: {
                "content-type": "application/json"
              }
            }
          )
          .then(response => {
            this.results = response.data.results;
            this.lOrigin = response.data.origin;
            this.lDestination = response.data.destination;
            this.nbCarpoolPlatform =
              response.data.nb > 0 ? response.data.nb : "-";
          })
          .catch(error => {
            console.log(error);
          })
          .finally(() => {
            this.loading = false;
            clearInterval(this.loadingInterval);
            this.loadingStep = -1;
          });
      } else if (this.lExternalId) {
        // if an externalId is provided, we load the corresponding proposal results
        this.loading = true;
        this.setLoadingStep();
        this.loadingInterval = setInterval(
          this.setLoadingStep,
          this.loadingDelay
        );
        if (this.filters === null) {
          this.filters = {
            page: this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          filters: this.filters
        };
        maxios
          .post(this.$t("externalUrl", { id: this.lExternalId }), postParams, {
            headers: {
              "content-type": "application/json"
            }
          })
          .then(response => {
            this.results = response.data;
            this.nbCarpoolPlatform =
              response.data.nb > 0
                ? response.data.nb
                : !isNaN(response.data.length)
                  ? response.data.length
                  : "-";
            this.lOrigin = {
              addressLocality: this.originLiteral
            };
            this.lDestination = {
              addressLocality: this.destinationLiteral
            };
            if (this.results.length > 0 && this.results[0].id) {
              this.lProposalId = this.results[0].id;
            }
          })
          .catch(error => {
            console.log(error);
          })
          .finally(() => {
            this.loading = false;
            clearInterval(this.loadingInterval);
            this.loadingStep = -1;
          });
      } else {
        // otherwise we send a proposal search
        this.loading = true;
        this.setLoadingStep();
        this.loadingInterval = setInterval(
          this.setLoadingStep,
          this.loadingDelay
        );
        if (this.filters === null) {
          this.filters = {
            page: this.page
          };
        } else {
          this.filters.page = this.page;
        }
        let postParams = {
          origin: this.origin,
          destination: this.destination,
          date: this.date,
          time: this.time,
          regular: this.regular,
          userId: this.user ? this.user.id : null,
          communityId: this.lCommunityId,
          filters: this.filters,
          role: this.role
        };
        maxios
          .post(this.$t("matchingUrl"), postParams, {
            headers: {
              "content-type": "application/json"
            }
          })
          .then(response => {
            this.results = response.data.results;
            this.searchId = response.data.searchId;
            this.nbCarpoolPlatform =
              response.data.nb > 0 ? response.data.nb : "-";
            if (this.results.length > 0 && this.results[0].id) {
              this.lProposalId = this.results[0].id;
            }
          })
          .catch(error => {
            console.log(error);
          })
          .finally(() => {
            this.loading = false;
            clearInterval(this.loadingInterval);
            this.loadingStep = -1;
          });
      }
    },
    searchExternalJourneys() {
      this.loadingExternal = true;
      let postParams = {
        driver: 1, // TO DO : Dynamic
        passenger: 0, // TO DO : Dynamic
        from_latitude: this.origin.latitude,
        from_longitude: this.origin.longitude,
        to_latitude: this.destination.latitude,
        to_longitude: this.destination.longitude,
        date: this.date,
        time: this.time,
        frequency: this.regular ? "regular" : "punctual"
      };
      maxios
        .post(this.$t("externalJourneyUrl"), postParams, {
          headers: {
            "content-type": "application/json"
          }
        })
        .then(response => {
          this.loadingExternal = false;
          this.externalRDEXResults = response.data;
          response.data.length > 0
            ? (this.nbCarpoolOther = response.data.length)
            : (this.nbCarpoolOther = "-");
        })
        .catch(error => {
          console.log(error);
        });
    },
    searchPTJourneys() {
      this.loadingPtResults = true;
      let postParams = {
        from_latitude: this.origin.latitude,
        from_longitude: this.origin.longitude,
        to_latitude: this.destination.latitude,
        to_longitude: this.destination.longitude,
        date: this.date
      };
      maxios
        .post(this.$t("ptSearchUrl"), postParams, {
          headers: {
            "content-type": "application/json"
          }
        })
        .then(response => {
          //console.error(response.data);
          this.loadingPtResults = false;
          response.data.member
            ? (this.ptResults = response.data.member)
            : (this.ptResults = []);
          response.data.member && response.data.member.length > 0
            ? (this.nbPtResults = response.data.member.length)
            : (this.nbPtResults = "-");
          this.ptSearchLoaded = true;
        })
        .catch(error => {
          console.log(error);
        });
    },
    contact(params) {
      // Creating a "virtual" new carpool thread
      const form = document.createElement("form");
      form.method = "post";
      form.action = this.$t("contactUrl");

      const paramsForm = {
        carpool: 1,
        idRecipient: params.idRecipient,
        shortFamilyName: params.shortFamilyName,
        givenName: params.givenName,
        avatar: params.avatar,
        origin: params.carpoolInfos.origin,
        destination: params.carpoolInfos.destination,
        askHistoryId: params.carpoolInfos.askHistoryId,
        frequency: params.carpoolInfos.criteria.frequency,
        fromDate: params.carpoolInfos.criteria.fromDate,
        fromTime: params.carpoolInfos.criteria.fromTime,
        monCheck: params.carpoolInfos.criteria.monCheck,
        tueCheck: params.carpoolInfos.criteria.tueCheck,
        wedCheck: params.carpoolInfos.criteria.wedCheck,
        thuCheck: params.carpoolInfos.criteria.thuCheck,
        friCheck: params.carpoolInfos.criteria.friCheck,
        satCheck: params.carpoolInfos.criteria.satCheck,
        sunCheck: params.carpoolInfos.criteria.sunCheck,
        adIdResult: params.adIdResult,
        matchingId: params.matchingId,
        proposalId: this.lProposalId,
        date: params.date,
        time: params.time,
        driver: params.driver,
        passenger: params.passenger,
        regular: params.regular
      };
      for (const key in paramsForm) {
        if (paramsForm.hasOwnProperty(key)) {
          const hiddenField = document.createElement("input");
          hiddenField.type = "hidden";
          hiddenField.name = key;
          hiddenField.value = paramsForm[key];
          form.appendChild(hiddenField);
        }
      }
      document.body.appendChild(form);
      form.submit();
    },
    launchCarpool(params) {
      maxios
        .post(this.$t("carpoolUrl"), params, {
          headers: {
            "content-type": "application/json"
          }
        })
        .then(response => {
          if (response.status == 200) {
            window.location = this.$t("mailboxUrl", {
              askId: response.data.askId
            });
          } else {
            console.log(response);
          }
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.carpoolDialog = false;
        });
    },
    launchExternalBooking(params) {
      params.message = this.carpoolStandardContactMessage;
      console.log(params);
      maxios
        .post(this.$t("bookingUrl"), params, {
          headers: {
            "content-type": "application/json"
          }
        })
        .then(response => {
          if (response.status == 200) {
            window.location = this.$t("carpoolMailBoxUrl");
          } else {
            console.log(response);
          }
        })
        .catch(error => {
          console.log(error);
        })
        .finally(() => {
          this.carpoolDialog = false;
        });
      this.bookingDialog = false;
      this.carpoolStandardContactDialog = false;
      this.carpoolStandardContactMessage = null;
      this.loadingCarpoolStandardContact = false;
    },
    updateFilters(data) {
      this.page = 1;
      this.filters = data;
      // Update the default filters also
      this.lCommunityId = this.filters.filters.community
        ? parseInt(this.filters.filters.community)
        : null;

      // If the communityid for a research has been modified, we need to post a new proposal for the search
      // We don't use the watch because it's excuted after updateFilters() is done (after the this.search...)
      if (this.lCommunityId !== this.lCommunityIdBak) {
        this.lProposalId = null;
      }
      this.search();
    },
    startNewSearch() {
      this.newSearch = true;
    },
    refreshProfileSummary() {
      this.profileSummaryRefresh = false;
    },
    mapRefreshed() {
      this.refreshMapMatchingJourney = false;
    },
    saveSearch() {
      formData(this.$t("saveSearch.route", { id: this.searchId }), null, "GET");
    },
    ptSearchTabChange() {
      if (this.ptSearch && !this.ptSearchLoaded) this.searchPTJourneys();
    },
    externalRdexJourneysTabChange() {
      if (this.externalRdexJourneys && !this.externalJourneysLoaded)
        this.searchExternalJourneys();
    }
  }
};
</script>
