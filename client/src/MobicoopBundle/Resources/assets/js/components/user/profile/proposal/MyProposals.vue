<template>
  <v-container
    fluid     
  >
    <v-row justify="center">
      <v-col>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('proposals.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localProposals.ongoing">
              <v-row
                v-for="proposal in localProposals.ongoing"
                :key="proposal.outward.id"
              >
                <v-col cols="12">
                  <Proposal
                    :proposal="proposal"
                    @proposal-deleted="deleteProposal"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('proposals.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localProposals.archived">
              <v-row
                v-for="proposal in localProposals.archived"
                :key="proposal.outward.id"
              >
                <v-col cols="12">
                  <Proposal
                    :proposal="proposal"
                    :is-archived="true"
                    @proposal-deleted="deleteProposal"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import { merge, omit } from "lodash";
import Translations from "@translations/components/user/profile/proposal/MyProposals.js";
import TranslationsClient from "@clientTranslations/components/user/profile/proposal/MyProposals.js";

import Proposal from "@components/user/profile/proposal/Proposal.vue";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    Proposal
  },
  props: {
    proposals: {
      type: Object,
      default: () => {}
    }
  },
  data(){
    return {
      localProposals: this.proposals
    }
  },
  methods: {
    deleteProposal(isArchived, id) {
      let type = isArchived ? "archived" : "ongoing";
      this.localProposals[type] = omit(this.localProposals[type], id);
    }
  }
}
</script>