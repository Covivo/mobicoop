import React from 'react';
import { LinearProgress } from '@material-ui/core';
import { useReference } from 'react-admin';

import { journeyRenderer } from '../../../../utils/renderers';

export const SolidaryJourney = ({ solidary, emptyText = '-', reverse = false }) => {
  const { loading, referenceRecord } = useReference({ id: solidary, reference: 'solidaries' });

  if (loading) {
    return <LinearProgress />;
  }

  return referenceRecord &&
    referenceRecord.origin &&
    referenceRecord.destination &&
    referenceRecord.origin.addressLocality &&
    referenceRecord.destination.addressLocality
    ? journeyRenderer(
        reverse
          ? {
              origin: referenceRecord.destination.addressLocality,
              destination: referenceRecord.origin.addressLocality,
            }
          : {
              origin: referenceRecord.origin.addressLocality,
              destination: referenceRecord.destination.addressLocality,
            }
      )
    : emptyText;
};
