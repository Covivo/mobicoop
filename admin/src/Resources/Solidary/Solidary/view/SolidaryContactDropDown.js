import React, { useState, useEffect } from 'react';
import { useNotify, useMutation } from 'react-admin';

import DropDownButton from '../../../../components/button/DropDownButton';
import { SolidaryMessagesModal } from '../SolidaryMessagesModal';
import { SolidarySMSModal } from '../SolidarySMSModal';
import { SolidaryFormalResponseModal } from './SolidaryFormalResponseModal';
import { useSolidary } from '../hooks/useSolidary';
import { SOLIDARYASK_STATUS_ASKED } from '../../../../constants/solidaryAskStatus';

const SMS_CONTACT_OPTION = 'Envoyer directement un SMS';
const MESSAGE_CONTACT_OPTION = 'Écrire vers sa messagerie';
const ASKFORRESPONSE_OPTION = 'Solliciter réponse formelle';

const resolveOptions = (solidary, ask) => {
  const options = [SMS_CONTACT_OPTION, MESSAGE_CONTACT_OPTION];
  if (!solidary) {
    return options;
  }

  return [
    ...options,
    ask && ask.status === SOLIDARYASK_STATUS_ASKED && ASKFORRESPONSE_OPTION,
  ].filter((x) => x);
};

const SolidaryPunctualFormalResponse = ({ outwardDate, solidarySolutionId, onClose }) => {
  const notify = useNotify();
  const [send] = useMutation(
    {},
    {
      onSuccess: () => {
        notify('Demande formelle envoyée', 'success');
        onClose();
      },
    }
  );

  useEffect(() => {
    return send({
      type: 'create',
      resource: 'solidary_formal_requests',
      payload: {
        data: {
          solidarySolution: `/solidary_solutions/${solidarySolutionId}`,
          outwardDate,
          returnDate: outwardDate, // return value = outward one
        },
      },
    });
  }, []);

  return null;
};

export const SolidaryContactDropDown = ({ solidaryId, solidarySolutionId, ...props }) => {
  const [contactType, setContactType] = useState(null);
  const { solidary, refresh } = useSolidary(`/solidaries/${solidaryId}`);

  const ask =
    solidary && solidary.asksList.find((i) => i.solidarySolutionId === solidarySolutionId);

  const handleCloseModal = () => {
    refresh();
    setContactType(null);
  };

  const contactTypeProps = {
    solidaryId,
    solidarySolutionId,
    onClose: handleCloseModal,
  };

  return (
    <>
      <DropDownButton
        {...props}
        options={resolveOptions(solidary, ask)}
        onSelect={setContactType}
      />
      {contactType === MESSAGE_CONTACT_OPTION && <SolidaryMessagesModal {...contactTypeProps} />}
      {contactType === SMS_CONTACT_OPTION && <SolidarySMSModal {...contactTypeProps} />}
      {contactType === ASKFORRESPONSE_OPTION &&
        solidary &&
        (solidary.frequency === 1 ? (
          <SolidaryPunctualFormalResponse
            {...contactTypeProps}
            outwardDate={solidary.outwardDatetime}
          />
        ) : (
          <SolidaryFormalResponseModal {...contactTypeProps} />
        ))}
    </>
  );
};
