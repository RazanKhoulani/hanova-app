import 'package:app/features/communication/data/models/message_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('bot payload maps structured topic and question options', () {
    final message = MessageModel.fromBotPayload({
      'message_id': 42,
      'answer': 'A database-backed answer',
      'options': ['First database question?', 'Second database question?'],
      'option_items': [
        {'type': 'topic', 'id': 5, 'label': 'Acne'},
        {'type': 'faq', 'id': 12, 'topic_id': 5, 'label': 'Why acne?'},
      ],
    });

    expect(message.id, 42);
    expect(message.isMe, isFalse);
    expect(message.text, 'A database-backed answer');
    expect(message.options, hasLength(2));
    expect(message.options!.first.type, 'topic');
    expect(message.options!.first.id, 5);
    expect(message.options!.first.label, 'Acne');
    expect(message.options!.last.type, 'faq');
    expect(message.options!.last.topicId, 5);
  });

  test('legacy string options remain supported for saved conversations', () {
    final message = MessageModel.fromJson({
      'text': 'Choose a question',
      'is_me': false,
      'options': ['First question?', 'Back to consultation topics'],
    });

    expect(message.options, hasLength(2));
    expect(message.options!.first.type, 'legacy');
    expect(message.options!.first.label, 'First question?');
  });
}
