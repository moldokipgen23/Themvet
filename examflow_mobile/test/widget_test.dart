import 'package:flutter_test/flutter_test.dart';

import 'package:themvet_mobile/main.dart';

void main() {
  testWidgets('App should render login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const ThemVetApp());
    await tester.pump();
    // Basic smoke test
    expect(find.text('ThemVet'), findsWidgets);
  });
}
